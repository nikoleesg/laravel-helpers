<?php

namespace Nikoleesg\LaravelHelpers\Traits;

use Illuminate\Support\Str;

trait HasTablePrefix
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        // If the table was explicitly set, respect it.
        if (isset($this->table)) {
            return $this->table;
        }

        // Otherwise, guess the table name and apply the prefix if set.
        $prefix = property_exists($this, 'tablePrefix') ? $this->tablePrefix : '';

        return $prefix.Str::snake(Str::pluralStudly(class_basename($this)));
    }
}
