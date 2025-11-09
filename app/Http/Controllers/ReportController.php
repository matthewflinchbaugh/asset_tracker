<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // We'll hard-code this for now.
    // We can create a settings page for it later.
    const LABOR_RATE_PER_HOUR = 75.00;

    /**
     * Show the cost analysis report.
     */
    public function costAnalysis()
    {
        // Get all active assets and eager-load their maintenance logs
        $assets = Asset::with('maintenanceLogs')
                       ->where('status', 'active')
                       ->orderBy('name')
                       ->get();

        // Pass the assets and the labor rate to the view
        return view('reports.cost_analysis', [
            'assets' => $assets,
            'laborRate' => self::LABOR_RATE_PER_HOUR
        ]);
    }
}
