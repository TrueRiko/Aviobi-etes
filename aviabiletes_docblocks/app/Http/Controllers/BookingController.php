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
use App\Models\Booking;
/**
 * Import required Laravel or application classes.
 */
use App\Models\AuditLog;
/**
 * Import required Laravel or application classes.
 */
use App\Mail\BookingConfirmation;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Http\Request;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\DB;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\Mail;

/**
 * Class BookingController
 *
 * Describe the purpose and responsibilities of this class.
 */
class BookingController extends Controller
{
    /**
 *   construct
 *
 * @param void
 * @return mixed
 */
public function __construct()
    {
        $this->middleware('auth');
    }

    /**
 * Index
 *
 * @param void
 * @return mixed
 */
public function index()
    {
        $bookings = auth()->user()->bookings()
            ->with('flight.aircraft')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        AuditLog::log('view_bookings_list');

        return view('bookings.index', compact('bookings'));
    }

    /**
 * Create
 *
 * @param Flight $flight
 * @return mixed
 */
public function create(Flight $flight)
    {
        AuditLog::log('start_booking', $flight);

        $seatMap = $flight->getSeatMap();
        $userBookingsCount = session('current_booking_count', 0);
        $maxSeats = 5 - $userBookingsCount;

        if ($maxSeats <= 0) {
            return redirect()->route('flights.show', $flight)
                ->with('error', __('messages.max_seats_reached'));
        }

        return view('bookings.create', compact('flight', 'seatMap', 'maxSeats'));
    }

    /**
 * Selectseats
 *
 * @param Request $request, Flight $flight
 * @return mixed
 */
public function selectSeats(Request $request, Flight $flight)
    {
        $request->validate([
            'seats' => 'required|array|min:1|max:5',
            'seats.*' => 'required|string|regex:/^[0-9]+[A-Z]$/',
        ]);

        $seats = $request->seats;
        $userBookingsCount = session('current_booking_count', 0);
        
        if (count($seats) + $userBookingsCount > 5) {
            return back()->with('error', __('messages.max_seats_exceeded'));
        }

        // Store selected seats in session
        session([
            'selected_flight' => $flight->id,
            'selected_seats' => $seats,
            'current_booking_count' => $userBookingsCount + count($seats),
        ]);

        AuditLog::log('select_seats', $flight, null, ['seats' => $seats]);

        return redirect()->route('bookings.review');
    }

    /**
 * Review
 *
 * @param void
 * @return mixed
 */
public function review()
    {
        $flightId = session('selected_flight');
        $seats = session('selected_seats');

        if (!$flightId || !$seats) {
            return redirect()->route('flights.index')
                ->with('error', __('messages.no_seats_selected'));
        }

        $flight = Flight::with('aircraft')->findOrFail($flightId);
        $totalPrice = $flight->base_price * count($seats);

        // Calculate seat types and prices
        $seatDetails = [];
        foreach ($seats as $seat) {
            $seatLetter = substr($seat, -1);
            $seatType = $this->determineSeatType($seatLetter, $flight->aircraft);
            
            $seatDetails[] = [
                'number' => $seat,
                'type' => $seatType,
                'price' => $flight->base_price,
            ];
        }

        return view('bookings.seat-selection', compact(
            'flight', 'seatDetails', 'totalPrice'
        ));
    }

    /**
 * Store
 *
 * @param Request $request
 * @return mixed
 */
public function store(Request $request)
    {
        $flightId = session('selected_flight');
        $seats = session('selected_seats');

        if (!$flightId || !$seats) {
            return redirect()->route('flights.index')
                ->with('error', __('messages.session_expired'));
        }

        $flight = Flight::findOrFail($flightId);

        DB::beginTransaction();
        try {
            $bookings = [];
            
            foreach ($seats as $seat) {
                // Check if seat is still available
                $existingBooking = Booking::where('flight_id', $flight->id)
                    ->where('seat', $seat)
                    ->whereIn('status', ['reserved', 'paid'])
                    ->first();

                if ($existingBooking) {
                    DB::rollback();
                    return redirect()->route('bookings.create', $flight)
                        ->with('error', __('messages.seat_no_longer_available', ['seat' => $seat]));
                }

                $seatLetter = substr($seat, -1);
                $seatType = $this->determineSeatType($seatLetter, $flight->aircraft);

                $booking = Booking::create([
                    'flight_id' => $flight->id,
                    'user_id' => auth()->id(),
                    'seat' => $seat,
                    'price' => $flight->base_price,
                    'status' => 'reserved',
                    'seat_type' => $seatType,
                ]);

                $bookings[] = $booking;

                AuditLog::log('create_booking', $booking, null, [
                    'flight_id' => $flight->id,
                    'seat' => $seat,
                    'price' => $flight->base_price,
                ]);
            }

            DB::commit();

            // Clear session
            session()->forget(['selected_flight', 'selected_seats']);

            // Send confirmation email
            Mail::to(auth()->user())->send(new BookingConfirmation($bookings, $flight));

            return redirect()->route('payment.create', ['bookings' => collect($bookings)->pluck('id')])
                ->with('success', __('messages.booking_created'));

        } catch (\Exception $e) {
            DB::rollback();
            
            AuditLog::log('booking_failed', null, null, [
                'error' => $e->getMessage(),
                'flight_id' => $flightId,
            ]);

            return redirect()->route('bookings.create', $flight)
                ->with('error', __('messages.booking_failed'));
        }
    }

    /**
 * Show
 *
 * @param Booking $booking
 * @return mixed
 */
public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        AuditLog::log('view_booking', $booking);

        $booking->load('flight.aircraft');

        return view('bookings.show', compact('booking'));
    }

    /**
 * Cancel
 *
 * @param Booking $booking
 * @return mixed
 */
public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if ($booking->status === 'paid') {
            return back()->with('error', __('messages.cannot_cancel_paid_booking'));
        }

        $oldStatus = $booking->status;
        $booking->cancel();

        AuditLog::log('cancel_booking', $booking, 
            ['status' => $oldStatus],
            ['status' => 'cancelled']
        );

        // Update session booking count
        $currentCount = session('current_booking_count', 0);
        session(['current_booking_count' => max(0, $currentCount - 1)]);

        return back()->with('success', __('messages.booking_cancelled'));
    }

    /**
 * Determineseattype
 *
 * @param $seatLetter, $aircraft
 * @return mixed
 */
private function determineSeatType($seatLetter, $aircraft)
    {
        $seatConfig = explode('-', $aircraft->seats_per_row);
        
        foreach ($seatConfig as $section) {
            if (strpos($section, $seatLetter) !== false) {
                $position = strpos($section, $seatLetter);
                
                if ($position === 0) return 'window';
                if ($position === strlen($section) - 1) return 'aisle';
                return 'middle';
            }
        }
        
        return 'middle';
    }
}
