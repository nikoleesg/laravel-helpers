<?php

namespace Nikoleesg\LaravelHelpers\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nikoleesg\LaravelHelpers\Database\Factories\TransactionFactory;
use Nikoleesg\LaravelHelpers\Traits\DateScopes;

class Transaction extends Model
{
    use DateScopes, HasFactory;

    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
