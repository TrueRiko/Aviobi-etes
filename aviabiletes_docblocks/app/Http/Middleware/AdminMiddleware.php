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
 * Class AdminMiddleware
 *
 * Describe the purpose and responsibilities of this class.
 */
class AdminMiddleware
{
    /**
 * Handle
 *
 * @param Request $request, Closure $next
 * @return mixed
 */
public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
