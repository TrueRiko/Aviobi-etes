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
 * Import required Laravel or application classes.
 */
use App\Models\AuditLog;

/**
 * Class LogUserActivity
 *
 * Describe the purpose and responsibilities of this class.
 */
class LogUserActivity
{
    /**
 * Handle
 *
 * @param Request $request, Closure $next
 * @return mixed
 */
public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $action = $request->route()->getName() ?? $request->path();
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
