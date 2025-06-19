<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Define which namespace this file belongs to.
 */
namespace App\Http\Controllers;

/**
 * Import required Laravel or application classes.
 */
use App\Models\Flight;
/**
 * Import required Laravel or application classes.
 */
use App\Models\AuditLog;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Http\Request;

/**
 * Class FlightController
 *
 * Describe the purpose and responsibilities of this class.
 */
class FlightController extends Controller
{
    /**
 * Index
 *
 * @param Request $request
 * @return mixed
 */
public function index(Request $request)
    {
        AuditLog::log('view_flights_list');

        $query = Flight::with('aircraft')->upcoming();

        // Apply filters
        if ($request->filled('origin')) {
            $query->fromOrigin($request->origin);
        }

        if ($request->filled('destination')) {
            $query->toDestination($request->destination);
        }

        if ($request->filled('date_from')) {
            $query->where('departure_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('departure_date', '<=', $request->date_to);
        }

        if ($request->filled('aircraft_type')) {
            $query->whereHas('aircraft', function ($q) /**
 * Import required Laravel or application classes.
 */
use ($request) {
                $q->where('model', $request->aircraft_type);
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'departure_date');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'price') {
            $query->orderBy('base_price', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $flights = $query->paginate(20)->appends($request->query());

        // Get filter options
        $origins = Flight::distinct()->pluck('origin')->sort();
        $destinations = Flight::distinct()->pluck('destination')->sort();
        $aircraftTypes = \App\Models\Aircraft::distinct()->pluck('model')->sort();

        return view('flights.index', compact(
            'flights', 'origins', 'destinations', 'aircraftTypes'
        ));
    }

    /**
 * Show
 *
 * @param Flight $flight
 * @return mixed
 */
public function show(Flight $flight)
    {
        AuditLog::log('view_flight_details', $flight);

        $flight->load('aircraft');
        $seatMap = $flight->getSeatMap();
        $availableSeats = $flight->getAvailableSeatsCount();

        return view('flights.show', compact('flight', 'seatMap', 'availableSeats'));
    }
}
