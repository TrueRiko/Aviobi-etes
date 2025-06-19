<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Define which namespace this file belongs to.
 */
namespace App\Models;

/**
 * Import required Laravel or application classes.
 */
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 *
 * Describe the purpose and responsibilities of this class.
 */
class AuditLog extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
 * User
 *
 * @param void
 * @return mixed
 */
public function user()
    {
        // Defines an Eloquent relationship: this model belongsTo User::class
return $this->belongsTo(User::class);
    }

    /**
 * Auditable
 *
 * @param void
 * @return mixed
 */
public function auditable()
    {
        return $this->morphTo('auditable', 'model_type', 'model_id');
    }

    public static function log($action, $model = null, $oldValues = null, $newValues = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
