<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormStructureController extends Controller
{
    /**
     * Static Patient Details section (always first).
     *
     * @return array<string, mixed>
     */
    private static function patientDetailsSection(): array
    {
        return [
            'id' => 'patient_details',
            'title' => 'Patient Details',
            'fields' => [
                [
                    'id' => 'patientName',
                    'label' => 'Enter Patient Name',
                    'type' => 'text',
                    'required' => true,
                    'errorMessage' => 'Name is required',
                ],
                [
                    'id' => 'age',
                    'label' => 'Age',
                    'type' => 'number',
                    'required' => true,
                    'errorMessage' => 'Age is required',
                    'validation' => ['min' => 1, 'max' => 120],
                ],
                [
                    'id' => 'gender',
                    'label' => 'Gender',
                    'type' => 'dropdown',
                    'required' => true,
                    'errorMessage' => 'Gender is required',
                    'options' => [
                        ['label' => 'Male', 'value' => 'male'],
                        ['label' => 'Female', 'value' => 'female'],
                        ['label' => 'Other', 'value' => 'other'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get form structure by department ID: static Patient Details first, then dynamic form(s).
     * GET /api/departments/{department}/form-structure
     * {department} = department id (integer) – resolves to department and loads forms by department name.
     */
    public function getByDepartment(Request $request, Department $department): JsonResponse
    {
        $sections = [self::patientDetailsSection()];

        $forms = Form::with(['fields.fieldType', 'fields.options', 'fields.attributeCondition.parentAttribute'])
            ->where('is_active', true)
            ->where('department', $department->name)
            ->orderBy('title')
            ->get();
        foreach ($forms as $form) {
            foreach ($this->formToSectionsByHeadings($form) as $section) {
                $sections[] = $section;
            }
        }

        return response()->json([
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
            ],
            'sections' => $sections,
        ]);
    }

    /**
     * Convert a Form into sections based on user-defined Section heading / Subsection heading fields.
     * Each heading starts a new section with id = heading field name, title = heading label.
     * Fields before the first heading go into one section with no main-form title.
     *
     * @return array<int, array<string, mixed>>
     */
    private function formToSectionsByHeadings(Form $form): array
    {
        $structure = $form->toFormStructure();
        $formFields = $structure['fields'] ?? [];
        $sections = [];
        $currentSection = null;
        $currentFields = [];

        foreach ($formFields as $f) {
            $type = $f['type'] ?? '';

            if ($type === 'heading' || $type === 'heading_sub') {
                if ($currentSection !== null || $currentFields !== []) {
                    $sections[] = [
                        'id' => $currentSection['id'] ?? $form->slug . '_intro',
                        'title' => $currentSection['title'] ?? '',
                        'fields' => $this->normalizeFieldsForApi($currentFields),
                    ];
                }
                $currentSection = [
                    'id' => $f['id'],
                    'title' => $f['label'],
                ];
                $currentFields = [];
                continue;
            }

            $currentFields[] = $f;
        }

        if ($currentSection !== null || $currentFields !== []) {
            $sections[] = [
                'id' => $currentSection['id'] ?? $form->slug . '_intro',
                'title' => $currentSection['title'] ?? '',
                'fields' => $this->normalizeFieldsForApi($currentFields),
            ];
        }

        return $sections;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFieldsForApi(array $fields): array
    {
        $out = [];
        foreach ($fields as $f) {
            $field = [
                'id' => $f['id'],
                'label' => $f['label'],
                'type' => $f['type'],
                'required' => (bool) ($f['required'] ?? false),
            ];
            if (! empty($f['placeholder'])) {
                $field['placeholder'] = $f['placeholder'];
            }
            if (! empty($f['helpText'])) {
                $field['helpText'] = $f['helpText'];
            }
            if ($field['required'] && empty($field['errorMessage'] ?? null)) {
                $field['errorMessage'] = $f['label'] . ' is required';
            }
            if (! empty($f['validation'])) {
                $field['validation'] = $f['validation'];
            }
            if (! empty($f['showIf'])) {
                $field['showIf'] = $f['showIf'];
            }
            if (! empty($f['options'])) {
                $field['options'] = $f['options'];
            }
            $out[] = $field;
        }
        return $out;
    }
}
