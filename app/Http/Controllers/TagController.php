<?php

namespace App\Http\Controllers;

use App\Models\Category; // <-- FIX: Import the Category model
use Illuminate\Http\Request; // <-- FIX: Import Request

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Pass an empty category object for form compatibility (optional, but good practice)
        return view('admin.categories.create', ['category' => new Category()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * * FIX: Type-hint the Category model to use Route-Model binding.
     * Laravel will automatically find the Category by its ID (from the URL 'tag' parameter).
     */
    public function edit(Category $tag) // Using $tag to match route parameter {tag}
    {
        // Rename $tag to $category for clarity in the view
        $category = $tag; 
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $tag) // Using $tag to match route parameter {tag}
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * (Stubbed for future use)
     */
    public function destroy(Category $tag)
    {
        // $tag->delete();
        // return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        return redirect()->route('categories.index')->with('error', 'Deleting is not enabled.');
    }
}
