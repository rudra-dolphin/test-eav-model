<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Entity;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * Edit a patient's submission (entity) values.
     */
    public function edit(Patient $patient, Entity $entity): View
    {
        if ((int) $entity->patient_id !== (int) $patient->id) {
            abort(404);
        }

        $entity->load([
            'form.fields.fieldType',
            'form.fields.options',
            'attributeValues',
        ]);

        $valuesByAttributeId = $entity->attributeValues->keyBy('attribute_id');

        return view('submissions.edit', [
            'patient' => $patient,
            'entity' => $entity,
            'form' => $entity->form,
            'fields' => $entity->form->fields,
            'valuesByAttributeId' => $valuesByAttributeId,
        ]);
    }

    /**
     * Update a patient's submission (entity) values.
     */
    public function update(Request $request, Patient $patient, Entity $entity): RedirectResponse
    {
        if ((int) $entity->patient_id !== (int) $patient->id) {
            abort(404);
        }

        $entity->load([
            'form.fields.fieldType',
            'form.fields.options',
            'attributeValues',
        ]);

        /** @var \Illuminate\Support\Collection<int, AttributeValue> $existing */
        $existing = $entity->attributeValues->keyBy('attribute_id');

        /** @var \Illuminate\Support\Collection<int, Attribute> $fields */
        $fields = $entity->form->fields;

        foreach ($fields as $attr) {
            $name = $attr->name;

            // Match the naming used in the public form submit page
            $value = $request->input($name);
            if ($value === null && $request->has($name . '_checkbox')) {
                $value = $request->input($name . '_checkbox');
            }

            $current = $existing->get($attr->id);

            // If field omitted/empty, remove the stored value (if any)
            if ($value === null || $value === '') {
                if ($current) {
                    $current->delete();
                }
                continue;
            }

            $valueCol = $attr->fieldType->value_column ?? 'value_text';
            if ($attr->fieldType->allows_multiple && is_array($value)) {
                $value = json_encode(array_values($value));
                $valueCol = 'value_text';
            }

            $payload = [
                'value_text' => null,
                'value_int' => null,
                'value_decimal' => null,
                'value_date' => null,
                'value_boolean' => null,
            ];
            $payload[$valueCol] = $value;

            $entity->attributeValues()->updateOrCreate(
                ['attribute_id' => $attr->id],
                $payload
            );
        }

        return redirect()
            ->route('patients.show', $patient)
            ->with('message', 'Submission updated.');
    }
}

