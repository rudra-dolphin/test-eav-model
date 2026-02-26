<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Entity;
use App\Models\Form;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormController extends Controller
{
    /**
     * List forms by department. Forms are associated with a department; list only populates when a department is selected.
     */
    public function index(Request $request): View
    {
        $departments = Department::orderBy('name')->pluck('name')->values();
        $hasOther = Form::where('is_active', true)->where(function ($q) {
            $q->whereNull('department')->orWhere('department', '');
        })->exists();

        $selectedDepartment = $request->input('department');
        $forms = collect();

        if ($request->filled('department')) {
            $query = Form::where('is_active', true)->orderBy('title');
            if ($selectedDepartment === '__other__') {
                $query->where(function ($q) {
                    $q->whereNull('department')->orWhere('department', '');
                });
            } else {
                $query->where('department', $selectedDepartment);
            }
            $forms = $query->get();
        }

        return view('forms.index', [
            'forms' => $forms,
            'departments' => $departments,
            'hasOther' => $hasOther,
            'selectedDepartment' => $selectedDepartment,
        ]);
    }

    /**
     * Show a form by slug and render dynamic fields.
     */
    public function show(string $slug): View|array
    {
        $form = Form::with(['fields.fieldType', 'fields.options'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Patients: same department as form when form has department, else all
        $patientsQuery = Patient::orderBy('name')->select(['id', 'name', 'patient_number', 'department']);
        if ($form->department) {
            $patientsQuery->where('department', $form->department);
        }
        $patients = $patientsQuery->get();

        return view('forms.show', [
            'form' => $form,
            'structure' => $form->toFormStructure(),
            'patients' => $patients,
        ]);
    }

    /**
     * Store form submission (create entity + attribute values).
     */
    public function store(Request $request, string $slug)
    {
        $form = Form::with(['fields.fieldType', 'fields.options'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $entity = new Entity;
        $entity->form_id = $form->id;
        $entity->patient_id = $request->input('patient_id') ?: null;
        $entity->status = 'submitted';
        $entity->save();

        foreach ($form->fields as $attr) {
            if (in_array($attr->fieldType->slug, ['heading', 'heading_sub'], true)) {
                continue;
            }
            $name = $attr->name;
            $value = $request->input($name);

            if ($value === null && $request->has($name . '_checkbox')) {
                $value = $request->input($name . '_checkbox');
            }

            if ($value === null || $value === '') {
                continue;
            }

            $valueCol = $attr->fieldType->value_column ?? 'value_text';
            if ($attr->fieldType->allows_multiple && is_array($value)) {
                $value = json_encode($value);
                $valueCol = 'value_text';
            }

            $data = ['attribute_id' => $attr->id];
            $data[$valueCol] = $value;
            $entity->attributeValues()->create($data);
        }

        return redirect()
            ->route('forms.show', ['slug' => $slug])
            ->with('message', 'Form submitted successfully.');
    }
}
