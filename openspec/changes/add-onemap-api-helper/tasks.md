## 1. Package Setup

- [x] 1.1 Add the Laravel HTTP client dependency if needed and verify `composer update illuminate/http --with-dependencies` or the chosen dependency update completes successfully.
- [x] 1.2 Add the `onemap` configuration section to `config/helpers.php` and verify published config shape includes base URL, credentials, manual token, cache key, expiry buffer, and timeout.
- [x] 1.3 Bind the OneMap client in `LaravelHelpersServiceProvider` and verify the client can be resolved from the Laravel container in a package test.

## 2. Client Implementation

- [x] 2.1 Create `src/OneMap/OneMapClient.php` with token resolution support and verify `getToken()` sends `POST /api/auth/post/getToken` with configured credentials using `Http::fake()`.
- [x] 2.2 Implement token caching with expiry buffer and verify tests cover cached-token reuse and expired-token refresh.
- [x] 2.3 Implement manual token precedence and verify per-call/configured tokens bypass credential authentication.
- [x] 2.4 Implement `search()` and verify tests assert endpoint path, `Authorization` header, `searchVal`, `returnGeom`, `getAddrDetails`, `pageNum`, and decoded response output.
- [x] 2.5 Implement `reverseGeocode()` for WGS84 only and verify tests assert endpoint path, `Authorization` header, `location=latitude,longitude`, optional `buffer`, `addressType`, `otherFeatures`, and decoded response output.
- [x] 2.6 Implement `convertSvy21ToWgs84()` and verify tests assert endpoint path, `Authorization` header, `X`, `Y`, and decoded response output.

## 3. Validation and Errors

- [x] 3.1 Add local validation for missing credentials and empty search text and verify invalid input throws before any HTTP request is sent.
- [x] 3.2 Add local validation for search page numbers and verify page numbers less than `1` throw before any HTTP request is sent.
- [x] 3.3 Add local validation for WGS84 latitude, longitude, and buffer and verify out-of-range values throw before any HTTP request is sent.
- [x] 3.4 Add local validation for reverse geocode address type and verify values other than `All` and `HDB` throw before any HTTP request is sent.

## 4. Documentation

- [x] 4.1 Refine `docs/onemap/implementation_plan.md` and verify it matches the OpenSpec scope and final implementation decisions.
- [x] 4.2 Refine `docs/onemap/user_manual.md` and verify examples match the implemented method signatures and config keys.
- [x] 4.3 Update `README.md` feature table and verify it links to `docs/onemap/user_manual.md` and the implementation plan.

## 5. Verification

- [x] 5.1 Run the targeted OneMap test file and verify it passes.
- [x] 5.2 Run `composer format` or `vendor/bin/pint --test` and verify formatting is clean.
- [x] 5.3 Run `composer analyse` and verify PHPStan/Larastan reports no errors.
- [x] 5.4 Run `composer test` and verify the full Pest suite passes.
- [x] 5.5 Run `openspec validate add-onemap-api-helper --strict` and verify the change artifacts are valid.
