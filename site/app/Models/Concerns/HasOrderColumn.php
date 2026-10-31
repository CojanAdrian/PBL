<?php

namespace App\Models\Concerns;

/**
 * New records go to the end of the manually sorted list.
 */
trait HasOrderColumn
{
    public static function bootHasOrderColumn(): void
    {
        static::creating(function (self $model) {
            $model->order_column ??= (static::max('order_column') ?? 0) + 1;
        });
    }
}
