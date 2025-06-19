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
 * Class Aircraft
 *
 * Describe the purpose and responsibilities of this class.
 */
class Aircraft extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'model',
        'total_seats',
        'rows',
        'seats_per_row',
    ];

    /**
 * Flights
 *
 * @param void
 * @return mixed
 */
public function flights()
    {
        // Defines an Eloquent relationship: this model hasMany Flight::class
return $this->hasMany(Flight::class);
    }

    /**
 * Getseatconfiguration
 *
 * @param void
 * @return mixed
 */
public function getSeatConfiguration()
    {
        // Convert "ABC-DEF" to ['ABC', 'DEF']
        return explode('-', $this->seats_per_row);
    }
}
