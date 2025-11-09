<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    /**
     * Display a listing of proposals for admins.
     */
    public function index()
    {
        $proposals = Proposal::with('user')->orderBy('status', 'asc')->orderBy('created_at', 'desc')->get();
        return view('admin.proposals.index', compact('proposals'));
    }

    /**
     * Show the form for creating a new proposal (for technicians).
     */
    public function create()
    {
        return view('technician.proposals.create');
    }

    /**
     * Store a newly created proposal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'reason' => 'required|string',
            'estimated_cost' => 'nullable|string|max:100',
        ]);

        Proposal::create([
            'user_id' => Auth::id(),
            'asset_name' => $validated['asset_name'],
            'reason' => $validated['reason'],
            'estimated_cost' => $validated['estimated_cost'],
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Proposal submitted successfully.');
    }

    /**
     * Update the status of a proposal (for admins).
     */
    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,denied',
            'admin_notes' => 'nullable|string',
        ]);

        $proposal->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'],
            'reviewed_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('proposals.index')->with('success', 'Proposal status updated.');
    }
}
