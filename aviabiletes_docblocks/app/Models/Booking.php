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
 * Import required Laravel or application classes.
 */
use Carbon\Carbon;

/**
 * Class Booking
 *
 * Describe the purpose and responsibilities of this class.
 */
class Booking extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'flight_id',
        'user_id',
        'seat',
        'price',
        'status',
        'seat_type',
        'reserved_until',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'price' => 'decimal:2',
        'reserved_until' => 'datetime',
    ];

    /**
 * Flight
 *
 * @param void
 * @return mixed
 */
public function flight()
    {
        // Defines an Eloquent relationship: this model belongsTo Flight::class
return $this->belongsTo(Flight::class);
    }

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
 * Isexpired
 *
 * @param void
 * @return mixed
 */
public function isExpired()
    {
        return $this->status === 'reserved' && 
               $this->reserved_until && 
               $this->reserved_until->isPast();
    }

    /**
 * Markaspaid
 *
 * @param void
 * @return mixed
 */
public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'reserved_until' => null,
        ]);
    }

    /**
 * Cancel
 *
 * @param void
 * @return mixed
 */
public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    protected static function booted()
    {
        static::creating(function ($booking) {
            if ($booking->status === 'reserved') {
                $booking->reserved_until = Carbon::now()->addMinutes(10);
            }
        });
    }
}
