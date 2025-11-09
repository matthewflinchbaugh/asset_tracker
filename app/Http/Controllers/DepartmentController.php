<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    		// 1. Fetch all departments from the database
		$departments = Department::orderBy('name')->get();

    		// 2. Return a view, passing the $departments variable
    		return view('admin.departments.index', compact('departments'));
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
	    return view('admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
	    $validated = $request->validate([
		    'name' => 'required|string|max:255|unique:departments',
		    'abbreviation' => 'required|string|max:3|unique:departments',
		    ]);
	Department::create($validated);
	return redirect()->route('departments.index')
		->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
	    return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
	    // 1. Validate the incoming data
    // We make the 'unique' rule ignore the current department's ID
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        'abbreviation' => 'required|string|max:3|unique:departments,abbreviation,' . $department->id,
    ]);

    // 2. Update the department
    $department->update($validated);

    // 3. Redirect back to the main list with a success message
    return redirect()->route('departments.index')
                     ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
	    $department->delete();

    	    return redirect()->route('departments.index')
                     ->with('success', 'Department deleted successfully.');
    }
}
