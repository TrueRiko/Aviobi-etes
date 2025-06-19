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
 * Class Payment
 *
 * Describe the purpose and responsibilities of this class.
 */
class Payment extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'is_primary',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'is_primary' => 'boolean',
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

    protected static function booted()
    {
        static::creating(function ($payment) {
            // If this is the first payment method, make it primary
            if (!$payment->user->payments()->exists()) {
                $payment->is_primary = true;
            }
        });

        static::updating(function ($payment) {
            // If setting as primary, unset other primary payments
            if ($payment->is_primary) {
                $payment->user->payments()
                    ->where('id', '!=', $payment->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
