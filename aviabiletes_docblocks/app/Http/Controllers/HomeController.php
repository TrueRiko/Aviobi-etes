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
 * Class HomeController
 *
 * Describe the purpose and responsibilities of this class.
 */
class HomeController extends Controller
{
    /**
 * Index
 *
 * @param Request $request
 * @return mixed
 */
public function index(Request $request)
    {
        AuditLog::log('view_homepage');

        $query = Flight::with('aircraft')
            ->upcoming();

        // Apply user preferences for sorting
        if (auth()->check()) {
            $sortPreference = auth()->user()->homepage_sort;
            
            if ($sortPreference === 'price') {
                $query->orderBy('base_price', 'asc');
            } else {
                $query->orderBy('departure_date', 'asc')
                      ->orderBy('departure_time', 'asc');
            }

            // Filter by preferred aircraft types
            $preferredAircraft = auth()->user()->preferred_aircraft_types;
            if (!empty($preferredAircraft)) {
                $query->whereHas('aircraft', function ($q) /**
 * Import required Laravel or application classes.
 */
use ($preferredAircraft) {
                    $q->whereIn('model', $preferredAircraft);
                });
            }
        } else {
            $query->orderBy('departure_date', 'asc')
                  ->orderBy('departure_time', 'asc');
        }

        // Search filters
        if ($request->filled('origin')) {
            $query->fromOrigin($request->origin);
        }

        if ($request->filled('destination')) {
            $query->toDestination($request->destination);
        }

        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        }

        $flights = $query->paginate(20);

        // Get unique origins and destinations for search filters
        $origins = Flight::distinct()->pluck('origin')->sort();
        $destinations = Flight::distinct()->pluck('destination')->sort();

        return view('home', compact('flights', 'origins', 'destinations'));
    }

    /**
 * Updatepreferences
 *
 * @param Request $request
 * @return mixed
 */
public function updatePreferences(Request $request)
    {
        $request->validate([
            'homepage_sort' => 'required|in:departure_time,price',
            'preferred_aircraft_types' => 'nullable|array',
            'preferred_aircraft_types.*' => 'exists:aircraft,model',
        ]);

        auth()->user()->update([
            'homepage_sort' => $request->homepage_sort,
            'preferred_aircraft_types' => $request->preferred_aircraft_types,
        ]);

        AuditLog::log('update_preferences', auth()->user(), 
            ['homepage_sort' => auth()->user()->getOriginal('homepage_sort')],
            ['homepage_sort' => $request->homepage_sort]
        );

        return redirect()->back()->with('success', __('messages.preferences_updated'));
    }
}
