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
 * Class Flight
 *
 * Describe the purpose and responsibilities of this class.
 */
class Flight extends Model
{
    /**
 * Import required Laravel or application classes.
 */
use HasFactory;

    protected $fillable = [
        'aircraft_id',
        'flight_number',
        'origin',
        'destination',
        'departure_date',
        'departure_time',
        'arrival_time',
        'base_price',
        'status',
    ];

    /**
 * Automatically cast attributes to appropriate types.
 */
protected $casts = [
        'departure_date' => 'date',
        'departure_time' => 'datetime:H:i',
        'arrival_time' => 'datetime:H:i',
        'base_price' => 'decimal:2',
    ];

    /**
 * Aircraft
 *
 * @param void
 * @return mixed
 */
public function aircraft()
    {
        // Defines an Eloquent relationship: this model belongsTo Aircraft::class
return $this->belongsTo(Aircraft::class);
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
 * Getavailableseatscount
 *
 * @param void
 * @return mixed
 */
public function getAvailableSeatsCount()
    {
        $totalSeats = $this->aircraft->total_seats;
        $bookedSeats = $this->bookings()
            ->whereIn('status', ['reserved', 'paid'])
            ->count();
        
        return $totalSeats - $bookedSeats;
    }

    /**
 * Getbookedseats
 *
 * @param void
 * @return mixed
 */
public function getBookedSeats()
    {
        return $this->bookings()
            ->whereIn('status', ['reserved', 'paid'])
            ->pluck('seat')
            ->toArray();
    }

    /**
 * Getseatmap
 *
 * @param void
 * @return mixed
 */
public function getSeatMap()
    {
        $aircraft = $this->aircraft;
        $bookedSeats = $this->getBookedSeats();
        $seatMap = [];
        
        $seatConfig = explode('-', $aircraft->seats_per_row);
        $totalSeatsPerRow = array_sum(array_map('strlen', $seatConfig));
        
        for ($row = 1; $row <= $aircraft->rows; $row++) {
            $rowSeats = [];
            $seatIndex = 0;
            
            foreach ($seatConfig as $section) {
                for ($i = 0; $i < strlen($section); $i++) {
                    $seatLetter = $section[$i];
                    $seatNumber = $row . $seatLetter;
                    
                    $rowSeats[] = [
                        'number' => $seatNumber,
                        'is_booked' => in_array($seatNumber, $bookedSeats),
                        'type' => $this->getSeatType($seatLetter, $section, $i),
                    ];
                }
                
                if ($seatIndex < count($seatConfig) - 1) {
                    $rowSeats[] = ['is_aisle' => true];
                }
                $seatIndex++;
            }
            
            $seatMap[] = $rowSeats;
        }
        
        return $seatMap;
    }

    /**
 * Getseattype
 *
 * @param $letter, $section, $position
 * @return mixed
 */
private function getSeatType($letter, $section, $position)
    {
        if ($position === 0) return 'window';
        if ($position === strlen($section) - 1) return 'aisle';
        return 'middle';
    }

    /**
 * Getdeparturedatetime
 *
 * @param void
 * @return mixed
 */
public function getDepartureDateTime()
    {
        return Carbon::parse($this->departure_date->format('Y-m-d') . ' ' . $this->departure_time);
    }

    /**
 * Scopeupcoming
 *
 * @param $query
 * @return mixed
 */
public function scopeUpcoming($query)
    {
        return $query->where('departure_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'cancelled');
    }

    /**
 * Scopefromorigin
 *
 * @param $query, $origin
 * @return mixed
 */
public function scopeFromOrigin($query, $origin)
    {
        return $query->where('origin', $origin);
    }

    /**
 * Scopetodestination
 *
 * @param $query, $destination
 * @return mixed
 */
public function scopeToDestination($query, $destination)
    {
        return $query->where('destination', $destination);
    }
}
