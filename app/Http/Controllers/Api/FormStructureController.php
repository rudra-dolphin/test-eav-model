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
                        'fields' => $this->nestFieldsByShowIf($this->normalizeFieldsForApi($currentFields)),
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
                'fields' => $this->nestFieldsByShowIf($this->normalizeFieldsForApi($currentFields)),
            ];
        }

        return $sections;
    }

    /**
     * Nest fields that have showIf under their parent. When parent has options, each option gets
     * its related children inside that option object (option.children). Otherwise children stay
     * in a top-level "children" object keyed by trigger value. Structure stays dynamic.
     *
     * @param array<int, array<string, mixed>> $fields Normalized flat fields (with showIf where applicable)
     * @return array<int, array<string, mixed>>
     */
    private function nestFieldsByShowIf(array $fields): array
    {
        $parentIdToChildren = [];
        foreach ($fields as $f) {
            $parentId = $f['showIf']['field'] ?? null;
            if ($parentId !== null && $parentId !== '') {
                $triggerValue = $f['showIf']['value'] ?? '';
                $parentIdToChildren[$parentId] = $parentIdToChildren[$parentId] ?? [];
                $parentIdToChildren[$parentId][] = ['triggerValue' => $triggerValue, 'field' => $this->fieldWithOptionsLast($f)];
            }
        }

        $result = [];
        foreach ($fields as $f) {
            if (! empty($f['showIf']['field'])) {
                continue;
            }
            $id = $f['id'] ?? '';
            $rawChildren = $parentIdToChildren[$id] ?? [];
            $childrenByTrigger = $rawChildren !== [] ? $this->groupChildrenByTriggerValue($rawChildren) : [];

            if (! empty($f['options']) && $childrenByTrigger !== []) {
                $f['options'] = $this->inlineChildrenIntoOptions($f['options'], $childrenByTrigger);
            } elseif ($childrenByTrigger !== []) {
                $f['children'] = $childrenByTrigger;
            }
            $result[] = $this->fieldWithOptionsLast($f);
        }
        return $result;
    }

    /**
     * Build options array where each option is { value, label, children } so values related to
     * an option (e.g. diabetes) sit inside that option as children. Same structure for outer array.
     *
     * @param array<int, array{value: string, label: string}> $options
     * @param array<string, array<int, array<string, mixed>>> $childrenByTrigger
     * @return array<int, array<string, mixed>>
     */
    private function inlineChildrenIntoOptions(array $options, array $childrenByTrigger): array
    {
        $out = [];
        foreach ($options as $opt) {
            $val = $opt['value'] ?? '';
            $item = [
                'value' => $val,
                'label' => $opt['label'] ?? $val,
            ];
            $item['children'] = $childrenByTrigger[$val] ?? [];
            $out[] = $item;
        }
        return $out;
    }

    /**
     * Group child fields by showIf.value (e.g. diabetes, hypertension) so values are under that key.
     *
     * @param array<int, array{triggerValue: string, field: array<string, mixed>}> $rawChildren
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupChildrenByTriggerValue(array $rawChildren): array
    {
        $byValue = [];
        foreach ($rawChildren as $c) {
            $v = $c['triggerValue'] ?? '';
            $byValue[$v] = $byValue[$v] ?? [];
            $byValue[$v][] = $c['field'];
        }
        return $byValue;
    }

    /**
     * Return field with key order so "options" is last (after "children") in JSON.
     *
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    private function fieldWithOptionsLast(array $field): array
    {
        $order = ['id', 'label', 'type', 'required', 'placeholder', 'helpText', 'errorMessage', 'validation', 'showIf', 'children', 'options'];
        $out = [];
        foreach ($order as $key) {
            if (array_key_exists($key, $field)) {
                $out[$key] = $field[$key];
            }
        }
        foreach ($field as $key => $value) {
            if (! array_key_exists($key, $out)) {
                $out[$key] = $value;
            }
        }
        return $out;
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
