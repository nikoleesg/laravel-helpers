# Reusable Workflow Classes User Manual

Welcome to the `nikoleesg/laravel-helpers` Workflow User Manual. This guide details the newly introduced reusable workflow design pattern, explaining *why* it exists, the *advantages* it brings to your development process, and *how* to use it effectively.

---

## 1. Why Use This Implementation?

When building durable execution workflows using `laravel-workflow`, you write "Activities" that represent individual steps (e.g., hitting an API, parsing a file, updating a database). Because distributed systems can fail mid-operation and jobs might retry natively, you must ensure that your activities are **idempotent** (safe to run multiple times without causing duplicate side effects).

Writing this idempotency tracking boilerplate manually for every single activity is tedious, prone to locking bugs, and difficult to standardize across a large team.

### Our Solution
We have abstracted this into two base classes:
- **`AbstractIdempotentActivity` (Layer 1)**: Forces the "Gate -> Side Effect -> Commit" 3-phase execution pattern for *any* task.
- **`AbstractModelActivity` (Layer 2)**: Tightly integrates with an Eloquent "Aggregation Model" and `spatie/laravel-model-states` to automatically handle database row-locking and idempotency checking based on the model's state.

## 2. Advantages to Development

1. **Standardization**: Every developer uses the exact same pattern for workflows, making code reviews vastly simpler.
2. **Built-in Safety**: The 3-phase execution isolates slow I/O (API calls) from database lock transactions, preventing database exhaustion.
3. **No Boilerplate**: You no longer need to write `DB::transaction(...)` or handle optimistic/pessimistic locking manually for standard states.
4. **Retry Intelligence**: Custom trait maps common exceptions (like `DomainException` or `InvalidArgumentException`) directly into `NonRetryableException`, preventing infinite, useless retries.

---

## 3. How to Use: Two Examples

### Example 1: Simple Workflow (Layer 1)
**Scenario**: You have a simple workflow that calls an external API. If the quota threshold hits a warning level, it sends an email. We don't need a dedicated database tracking model for this; we just need to ensure the email isn't sent twice if the activity retries.

**Implementation**: We use `AbstractIdempotentActivity`.

```php
use Nikoleesg\LaravelHelpers\Workflows\Activities\AbstractIdempotentActivity;
use Nikoleesg\LaravelHelpers\Workflows\Exceptions\SkipActivityException;
use App\Services\QuotaApi;
use Illuminate\Support\Facades\Cache;

class CheckQuotaAndNotifyActivity extends AbstractIdempotentActivity
{
    /**
     * Phase 1: Pre-checks. We use Cache to prevent duplicate emails 
     * in case the Laravel queue decides to retry a successful job.
     */
    protected function gate(mixed ...$args): void
    {
        $tenantId = $args[0];
        
        // If we firmly know we already did this today, skip the whole activity safely.
        if (Cache::has("quota_checked_today_{$tenantId}")) {
            throw new SkipActivityException();
        }
    }

    /**
     * Phase 2: The actual Side Effect (IO operations)
     * No DB transactions or locks are active here.
     */
    protected function sideEffect(mixed ...$args): void
    {
        $tenantId = $args[0];
        $api = app(QuotaApi::class);
        $usage = $api->getUsage($tenantId);

        if ($usage->exceedsThreshold(80)) {
            // Send email
            \Mail::to('admin@company.com')->send(new QuotaWarningEmail($tenantId));
        }
    }

    /**
     * Phase 3: Commit the action.
     * Record the completion successfully.
     */
    protected function commit(mixed ...$args): void
    {
        $tenantId = $args[0];
        // Mark as done so Phase 1 catches it next time
        Cache::put("quota_checked_today_{$tenantId}", true, now()->endOfDay());
    }
}
```

---

### Example 2: Complex Usage (Layer 2)
**Scenario**: We want to rewrite the demo `DecodeSurveyResponseActivity` using our new standard approach. In the demo, there is a `SurveyResponseImport` model acting as the central ledger (Aggregator Model), and it uses `spatie/laravel-model-states` to move states natively.

**Implementation**: We transition to using `AbstractModelActivity`. Notice how much boilerplate vanishes compared to the original manual implementation!

#### Setup (The Model)
Ensure your model implements the `WorkflowAggregator` contract:

```php
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Workflows\Contracts\WorkflowAggregator;
use Spatie\ModelStates\HasStates;

class SurveyResponseImport extends Model implements WorkflowAggregator
{
    use HasStates;

    // ...

    public function getWorkflowStateColumn(): string
    {
        return 'state'; // The database column tracked by spatie/laravel-model-states
    }
}
```

#### The Activity
We drastically simplify the activity. We define the model class, the target state after this activity completes, and write the custom side effects. The abstract class handles all database locks (`lockForUpdate`), `canTransitionTo()` checks, and the final state transition.

```php
use App\Models\Survey\SurveyResponseImport;
use App\States\SurveyResponseImport\ImportStateDecoded;
use Nikoleesg\LaravelHelpers\Workflows\Activities\AbstractModelActivity;
use Illuminate\Database\Eloquent\Model;

class DecodeSurveyResponseActivity extends AbstractModelActivity
{
    /**
     * Link this Activity to the specific Eloquent Model type.
     */
    protected function getModelClass(): string
    {
        return SurveyResponseImport::class;
    }

    /**
     * The target Spatie State we want to reach. The abstract handler will:
     * 1. Assert we can transition to this state.
     * 2. Automatically skip if we are already at or past this state.
     * 3. Perform the transition in the commit() phase automatically.
     */
    protected function getTargetState(): ?string
    {
        return ImportStateDecoded::class;
    }

    /**
     * Phase 2: Perform the specific Side Effects.
     * The model is provided here directly, safely unlocked so API calls are fast.
     */
    protected function performAction(Model $model, mixed ...$args): void
    {
        /** @var SurveyResponseImport $model */
        
        $payloadReader = app(OdkSubmissionPayloadReader::class);
        $payload = $payloadReader->getSubmissionPayload($model);
        
        // Complex logic: parsing XML/JSON
        $payloadData = OdkSubmissionParser::parse($payload);
        
        // Decoded data saved directly. 
        // Note: The main "state" transition is handled automatically in commit().
        $model->update([
            'submission_data' => $payloadData
        ]);
    }
}
```

As illustrated above, `AbstractModelActivity` entirely stripped away custom `DB::transaction` wrappers, idempotency sequence `order()` checks, and custom commit implementations. As a developer, you only write the unique operational code in `performAction()`.
