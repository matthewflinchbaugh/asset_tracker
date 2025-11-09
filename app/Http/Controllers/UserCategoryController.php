<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tag; // Use the Tag model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserCategoryController extends Controller
{
    /**
     * Show the form for managing a user's category visibility.
     */
    public function edit(User $user)
    {
        // FIX: Renamed variable from $categories to $tags
        $tags = Tag::orderBy('name')->get();
        
        // Get an array of IDs of categories this user already sees
        $assignedCategories = $user->visibleCategories->pluck('id')->toArray();
        
        // FIX: Pass 'tags' to the view, not 'categories'
        return view('admin.users.visibility', compact('user', 'tags', 'assignedCategories'));
    }

    /**
     * Update the user's category visibility.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            // categories_ids is an array of category IDs, must be integers and exist in the categories table
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id', // Still checks 'categories' table
        ]);

        // Sync attaches the new list of categories and detaches any that were unchecked.
        $user->visibleCategories()->sync($request->input('category_ids', []));

        return redirect()->route('users.index')
                         ->with('success', "Tag visibility updated for {$user->name}.");
    }
}
