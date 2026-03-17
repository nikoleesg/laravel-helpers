<?php

namespace Nikoleesg\LaravelHelpers\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Database\Factories\CustomTransactionFactory;
use Nikoleesg\LaravelHelpers\Traits\DateScopes;

class CustomTransaction extends Model
{
    use DateScopes, HasFactory;

    protected static function newFactory()
    {
        return CustomTransactionFactory::new();
    }

    public $timestamps = false;

    const CREATED_AT = 'custom_created_at';
}
