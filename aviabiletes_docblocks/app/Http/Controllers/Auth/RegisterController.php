<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Define which namespace this file belongs to.
 */
namespace App\Http\Controllers\Auth;

/**
 * Import required Laravel or application classes.
 */
use App\Http\Controllers\Controller;
/**
 * Import required Laravel or application classes.
 */
use App\Models\User;
/**
 * Import required Laravel or application classes.
 */
use App\Models\Role;
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
use Illuminate\Support\Facades\Hash;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\Auth;

/**
 * Class RegisterController
 *
 * Describe the purpose and responsibilities of this class.
 */
class RegisterController extends Controller
{
    /**
 * Showregistrationform
 *
 * @param void
 * @return mixed
 */
public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
 * Register
 *
 * @param Request $request
 * @return mixed
 */
public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passengerRole = Role::where('name', 'Passenger')->first();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $passengerRole->id,
            'preferred_language' => app()->getLocale(),
        ]);

        Auth::login($user);

        AuditLog::log('user_registration', $user, null, [
            'email' => $user->email,
            'role' => 'Passenger',
        ]);

        return redirect()->route('home')
            ->with('success', __('messages.registration_successful'));
    }
}
