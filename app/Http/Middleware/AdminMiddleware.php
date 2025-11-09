<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next)
{
    // Check if user is authenticated AND if their role is 'admin'
    if (auth()->check() && auth()->user()->role === 'admin') {
        // User is an admin, allow them to proceed
        return $next($request);
    }

    // User is not an admin, send them back to their dashboard with an error
    return redirect('/dashboard')->with('error', 'You do not have permission to access that page.');
}
}
