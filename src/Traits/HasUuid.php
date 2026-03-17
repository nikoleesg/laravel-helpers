<?php

namespace Nikoleesg\LaravelHelpers\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait to generate UUIDs automatically.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            $uuidColumn = $model->getUuidColumn();

            if (empty($model->{$uuidColumn})) {
                $model->{$uuidColumn} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the name of the UUID column.
     */
    public function getUuidColumn(): string
    {
        return property_exists($this, 'uuidColumn') ? $this->uuidColumn : 'uuid';
    }

    /**
     * Determine if the model uses the UUID column as the route key.
     */
    public function useUuidForRouteKey(): bool
    {
        return property_exists($this, 'useUuidForRouteKey') ? $this->useUuidForRouteKey : false;
    }

    /**
     * Determine if the model uses the UUID column as the primary key.
     */
    public function useUuidAsPrimaryKey(): bool
    {
        return property_exists($this, 'useUuidAsPrimaryKey') ? $this->useUuidAsPrimaryKey : false;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        if ($this->useUuidForRouteKey()) {
            return $this->getUuidColumn();
        }

        return parent::getRouteKeyName();
    }

    /**
     * Get the primary key for the model.
     */
    public function getKeyName(): string
    {
        if ($this->useUuidAsPrimaryKey()) {
            return $this->getUuidColumn();
        }

        return parent::getKeyName();
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        if ($this->useUuidAsPrimaryKey()) {
            return false;
        }

        return parent::getIncrementing();
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        if ($this->useUuidAsPrimaryKey()) {
            return 'string';
        }

        return parent::getKeyType();
    }
}
