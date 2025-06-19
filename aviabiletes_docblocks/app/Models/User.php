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
use Illuminate\Foundation\Auth\User as Authenticatable;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Notifications\Notifiable;
/**
 * Import required Laravel or application classes.
 */
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * Describe the purpose and responsibilities of this class.
 */
class User extends Authenticatable
{
    /**
 * Import required Laravel or application classes.
 */
use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'preferred_language',
        'homepage_sort',
        'preferred_aircraft_types',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferred_aircraft_types' => 'array',
    ];

    /**
 * Role
 *
 * @param void
 * @return mixed
 */
public function role()
    {
        // Defines an Eloquent relationship: this model belongsTo Role::class
return $this->belongsTo(Role::class);
    }

    /**
 * Bookings
 *
 * @param void
 * @return mixed
 */
public function bookings()
    {
        // Defines an Eloquent relationship: this model hasMany Booking::class
return $this->hasMany(Booking::class);
    }

    /**
 * Payments
 *
 * @param void
 * @return mixed
 */
public function payments()
    {
        // Defines an Eloquent relationship: this model hasMany Payment::class
return $this->hasMany(Payment::class);
    }

    /**
 * Notifications
 *
 * @param void
 * @return mixed
 */
public function notifications()
    {
        // Defines an Eloquent relationship: this model hasMany Notification::class
return $this->hasMany(Notification::class);
    }

    /**
 * Isadmin
 *
 * @param void
 * @return mixed
 */
public function isAdmin()
    {
        return $this->role->name === 'Administrator';
    }

    /**
 * Ispassenger
 *
 * @param void
 * @return mixed
 */
public function isPassenger()
    {
        return $this->role->name === 'Passenger';
    }

    /**
 * Getprimarypayment
 *
 * @param void
 * @return mixed
 */
public function getPrimaryPayment()
    {
        return $this->payments()->where('is_primary', true)->first();
    }
}
