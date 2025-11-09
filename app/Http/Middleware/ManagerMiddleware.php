<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     * Allows access for 'manager' OR 'admin' roles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->role == 'manager' || $user->role == 'admin')) {
            return $next($request);
        }

        return redirect('dashboard')->with('error', 'You do not have permission to access that page.');
    }
}
