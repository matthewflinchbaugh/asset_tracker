<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianPendingAssetController extends Controller
{
    /**
     * Show the list of pending assets created by the logged-in technician.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only technicians should see this page
        if ($user->role !== 'technician') {
            abort(403, 'Only technicians can view their pending assets.');
        }

        $search = $request->input('search');

        $query = Asset::with(['department', 'category'])
            ->where('status', 'pending_approval')
            ->where('created_by_user_id', $user->id);

        if ($search) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('asset_tag_id', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($deptQuery) use ($search) {
                        $deptQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('technician.assets.pending', [
            'assets' => $assets,
            'search' => $search,
        ]);
    }
}

