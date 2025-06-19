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
 * Class Notification
 *
 * Describe the purpose and responsibilities of this class.
 */
class Notification extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_id',
        'type',
        'message',
        'is_read',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'is_read' => 'boolean',
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
 * Markasread
 *
 * @param void
 * @return mixed
 */
public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
