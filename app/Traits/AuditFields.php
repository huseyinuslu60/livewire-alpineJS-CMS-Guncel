<?php

namespace App\Traits;

trait AuditFields
{
    /**
     * Boot the trait and register observers
     */
    public static function bootAuditFields(): void
    {
        static::creating(function ($model) {
            if (auth()->check() && in_array('created_by', $model->getFillable())) {
                $model->created_by = auth()->id();
            }
            if (auth()->check() && in_array('updated_by', $model->getFillable())) {
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && in_array('updated_by', $model->getFillable())) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check() && in_array('deleted_by', $model->getFillable())) {
                // For soft deletes, update the model before deletion
                $traits = class_uses_recursive(get_class($model));
                if (isset($traits[\Illuminate\Database\Eloquent\SoftDeletes::class])) {
                    $model->deleted_by = auth()->id();
                    $model->saveQuietly(); // Save without triggering events
                }
            }
        });
    }
}
