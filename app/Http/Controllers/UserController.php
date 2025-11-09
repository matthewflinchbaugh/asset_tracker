<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource (all users).
     */
    public function index()
    {
        // Fetch all users except the current authenticated user
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (Admin function).
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user (Admin function).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => [
                'required',
                Rule::in(['admin', 'manager', 'technician']),
            ],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
                         ->with('success', "User {$validated['name']} created successfully as {$validated['role']}.");
    }

    /**
     * Show the form for editing the specified user's role.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user's role in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validate the role change
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in(['admin', 'manager', 'technician']),
            ],
        ]);

        // 2. Update the user's role
        $user->update([
            'role' => $validated['role'],
        ]);

        // 3. Redirect back to the index page
        return redirect()->route('users.index')
                         ->with('success', "Role for {$user->name} updated to {$validated['role']}.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Safety check: Prevent deletion of yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                             ->with('error', 'You cannot delete your own account.');
        }

        // NOTE: The database now handles setting foreign keys to NULL.
        // We only need to detach the pivot table entries (visibility) before deleting.
        $user->visibleCategories()->detach();

        $user->delete();
        
        return redirect()->route('users.index')
                         ->with('success', 'User deleted successfully. Associated records were archived.');
    }
}
