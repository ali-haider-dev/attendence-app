<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Exception;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->get();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_departments,name',
            'code' => 'required|string|max:50|unique:tbl_departments,code',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            Department::create($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Department created successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tbl_departments,name,' . $department->id,
            'code' => 'required|string|max:50|unique:tbl_departments,code,' . $department->id,

        ]);

        // Checkbox handling
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            // Update department
            $department->update($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Department updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Cannot delete department with active employees!');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully!');
    }
}