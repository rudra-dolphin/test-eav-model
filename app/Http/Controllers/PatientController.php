<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Entity;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * List all patients, optionally filtered by department or search.
     */
    public function index(Request $request): View
    {
        $query = Patient::query()->orderBy('name');

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('patient_number', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $patients = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->pluck('name')->values();

        return view('patients.index', [
            'patients' => $patients,
            'departments' => $departments,
        ]);
    }

    /**
     * Show form to add a new patient.
     */
    public function create(): View
    {
        $departments = Department::orderBy('name')->pluck('name')->values();
        return view('patients.create', ['departments' => $departments]);
    }

    /**
     * Store a new patient.
     */
    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'patient_number' => 'nullable|string|max:50|unique:patients,patient_number',
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
        ]);

        Patient::create($valid);

        return redirect()
            ->route('patients.index')
            ->with('message', 'Patient added.');
    }

    /**
     * Show a patient and their submitted forms (submissions).
     */
    public function show(Patient $patient): View
    {
        $submissions = Entity::with('form')
            ->where('patient_id', $patient->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('patients.show', [
            'patient' => $patient,
            'submissions' => $submissions,
        ]);
    }
}
