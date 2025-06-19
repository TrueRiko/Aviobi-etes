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
use App\Models\AuditLog;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Http\Request;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController
 *
 * Describe the purpose and responsibilities of this class.
 */
class LoginController extends Controller
{
    /**
 * Showloginform
 *
 * @param void
 * @return mixed
 */
public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
 * Login
 *
 * @param Request $request
 * @return mixed
 */
public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditLog::log('user_login');

            // Set locale based on user preference
            app()->setLocale(auth()->user()->preferred_language);
            session(['locale' => auth()->user()->preferred_language]);

            if (auth()->user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        AuditLog::log('failed_login', null, null, ['email' => $request->email]);

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
 * Logout
 *
 * @param Request $request
 * @return mixed
 */
public function logout(Request $request)
    {
        AuditLog::log('user_logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
