<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * List all departments.
     */
    public function index(): View
    {
        $departments = Department::orderBy('name')->get();

        return view('departments.index', ['departments' => $departments]);
    }

    /**
     * Show form to add a department.
     */
    public function create(): View
    {
        return view('departments.create');
    }

    /**
     * Store a new department.
     */
    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string|max:255',
        ]);

        Department::create($valid);

        return redirect()
            ->route('departments.index')
            ->with('message', 'Department added. You can now assign it to forms and patients.');
    }
}
