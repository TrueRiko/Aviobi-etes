<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Define which namespace this file belongs to.
 */
namespace App\Http\Middleware;

/**
 * Import required Laravel or application classes.
 */
use Closure;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Http\Request;

/**
 * Class SetLocale
 *
 * Describe the purpose and responsibilities of this class.
 */
class SetLocale
{
    /**
 * Handle
 *
 * @param Request $request, Closure $next
 * @return mixed
 */
public function handle(Request $request, Closure $next)
    {
        // Priority: URL parameter > session > user preference > browser preference > default
        
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (in_array($locale, ['en', 'lv'])) {
                app()->setLocale($locale);
                session(['locale' => $locale]);
                
                if (auth()->check()) {
                    auth()->user()->update(['preferred_language' => $locale]);
                }
            }
        } elseif (session()->has('locale')) {
            app()->setLocale(session('locale'));
        } elseif (auth()->check()) {
            app()->setLocale(auth()->user()->preferred_language);
        } else {
            // Check browser language preference
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($browserLang, ['en', 'lv'])) {
                app()->setLocale($browserLang);
            }
        }

        return $next($request);
    }
}
