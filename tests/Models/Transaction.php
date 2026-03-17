<?php

namespace Nikoleesg\LaravelHelpers\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Traits\DateScopes;
use Nikoleesg\LaravelHelpers\Database\Factories\TransactionFactory;

class Transaction extends Model
{
    use HasFactory, DateScopes;

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
