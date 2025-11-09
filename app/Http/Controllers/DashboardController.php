<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admins see the full admin dashboard
            return view('admin.dashboard');
        
        } elseif ($user->role === 'manager') {
            // Managers see their specific dashboard
            return view('manager.dashboard');
        
        } elseif ($user->role === 'technician') {
            // Technicians see their specific dashboard
            return view('technician.dashboard');
        }

        // Default fallback (shouldn't be reached)
        return view('dashboard');
    }
}
