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
use App\Models\Booking;
/**
 * Import required Laravel or application classes.
 */
use App\Models\Payment;
/**
 * Import required Laravel or application classes.
 */
use App\Models\AuditLog;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Http\Request;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentController
 *
 * Describe the purpose and responsibilities of this class.
 */
class PaymentController extends Controller
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
 * Create
 *
 * @param Request $request
 * @return mixed
 */
public function create(Request $request)
    {
        $bookingIds = $request->query('bookings', []);
        if (!is_array($bookingIds)) {
            $bookingIds = explode(',', $bookingIds);
        }

        $bookings = Booking::whereIn('id', $bookingIds)
            ->where('user_id', auth()->id())
            ->where('status', 'reserved')
            ->with('flight')
            ->get();

        if ($bookings->isEmpty()) {
            return redirect()->route('bookings.index')
                ->with('error', __('messages.no_bookings_to_pay'));
        }

        // Check if any bookings have expired
        $expiredBookings = $bookings->filter(function ($booking) {
            return $booking->isExpired();
        });

        if ($expiredBookings->isNotEmpty()) {
            // Cancel expired bookings
            foreach ($expiredBookings as $booking) {
                $booking->cancel();
            }

            return redirect()->route('bookings.index')
                ->with('error', __('messages.bookings_expired'));
        }

        $totalAmount = $bookings->sum('price');
        $paymentMethods = auth()->user()->payments;

        AuditLog::log('view_payment_page', null, null, [
            'booking_ids' => $bookingIds,
            'total_amount' => $totalAmount,
        ]);

        return view('payment.create', compact('bookings', 'totalAmount', 'paymentMethods'));
    }

    /**
 * Store
 *
 * @param Request $request
 * @return mixed
 */
public function store(Request $request)
    {
        $request->validate([
            'booking_ids' => 'required|array',
            'booking_ids.*' => 'exists:bookings,id',
            'payment_method' => 'required|exists:payments,id',
        ]);

        $bookings = Booking::whereIn('id', $request->booking_ids)
            ->where('user_id', auth()->id())
            ->where('status', 'reserved')
            ->get();

        if ($bookings->isEmpty()) {
            return redirect()->route('bookings.index')
                ->with('error', __('messages.no_bookings_to_pay'));
        }

        $paymentMethod = Payment::where('id', $request->payment_method)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        DB::beginTransaction();
        try {
            foreach ($bookings as $booking) {
                // Check if booking hasn't expired
                if ($booking->isExpired()) {
                    DB::rollback();
                    return redirect()->route('bookings.index')
                        ->with('error', __('messages.booking_expired_during_payment'));
                }

                $booking->markAsPaid();

                AuditLog::log('complete_payment', $booking, 
                    ['status' => 'reserved'],
                    ['status' => 'paid', 'payment_method_id' => $paymentMethod->id]
                );
            }

            DB::commit();

            // Clear booking count from session after successful payment
            session()->forget('current_booking_count');

            return redirect()->route('bookings.index')
                ->with('success', __('messages.payment_successful'));

        } catch (\Exception $e) {
            DB::rollback();

            AuditLog::log('payment_failed', null, null, [
                'error' => $e->getMessage(),
                'booking_ids' => $request->booking_ids,
            ]);

            return redirect()->route('payment.create', ['bookings' => implode(',', $request->booking_ids)])
                ->with('error', __('messages.payment_failed'));
        }
    }

    /**
 * Methods
 *
 * @param void
 * @return mixed
 */
public function methods()
    {
        $paymentMethods = auth()->user()->payments;

        return view('payment.methods', compact('paymentMethods'));
    }

    /**
 * Addmethod
 *
 * @param void
 * @return mixed
 */
public function addMethod()
    {
        return view('payment.add-method');
    }

    /**
 * Storemethod
 *
 * @param Request $request
 * @return mixed
 */
public function storeMethod(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'is_primary' => 'boolean',
        ]);

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        AuditLog::log('add_payment_method', $payment, null, [
            'bank_name' => $payment->bank_name,
            'is_primary' => $payment->is_primary,
        ]);

        return redirect()->route('payment.methods')
            ->with('success', __('messages.payment_method_added'));
    }

    /**
 * Deletemethod
 *
 * @param Payment $payment
 * @return mixed
 */
public function deleteMethod(Payment $payment)
    {
        $this->authorize('delete', $payment);

        if ($payment->is_primary && auth()->user()->payments()->count() > 1) {
            // Set another payment method as primary
            auth()->user()->payments()
                ->where('id', '!=', $payment->id)
                ->first()
                ->update(['is_primary' => true]);
        }

        AuditLog::log('delete_payment_method', $payment, [
            'bank_name' => $payment->bank_name,
            'account_number' => $payment->account_number,
        ]);

        $payment->delete();

        return redirect()->route('payment.methods')
            ->with('success', __('messages.payment_method_deleted'));
    }
}
