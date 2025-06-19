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
 * Class Role
 *
 * Describe the purpose and responsibilities of this class.
 */
class Role extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = ['name'];

    /**
 * Users
 *
 * @param void
 * @return mixed
 */
public function users()
    {
        // Defines an Eloquent relationship: this model hasMany User::class
return $this->hasMany(User::class);
    }
}
