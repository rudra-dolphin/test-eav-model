<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeCondition;
use App\Models\AttributeOption;
use App\Models\Department;
use App\Models\FieldType;
use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    /**
     * List all forms (builder entry).
     */
    public function index(): View
    {
        $forms = Form::withCount('fields')->orderBy('title')->get();

        return view('forms.build.index', ['forms' => $forms]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $departments = Department::orderBy('name')->pluck('name')->values();
        return view('forms.build.create', ['departments' => $departments]);
    }

    /**
     * Store new form.
     */
    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'slug' => 'required|string|max:100|unique:forms,slug',
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        $valid['is_active'] = $request->boolean('is_active', true);

        $form = Form::create($valid);

        return redirect()
            ->route('forms.build.edit', $form)
            ->with('message', 'Form created. Add fields below.');
    }

    /**
     * Edit form and its fields.
     */
    public function edit(Form $form): View
    {
        $form->load(['fields.fieldType', 'fields.options']);
        $departments = Department::orderBy('name')->pluck('name')->values();

        return view('forms.build.edit', [
            'form' => $form,
            'fieldTypes' => FieldType::orderBy('name')->get(),
            'departments' => $departments,
        ]);
    }

    /**
     * Update form.
     */
    public function update(Request $request, Form $form): RedirectResponse
    {
        $valid = $request->validate([
            'slug' => 'required|string|max:100|unique:forms,slug,' . $form->id,
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        $valid['is_active'] = $request->boolean('is_active', true);

        $form->update($valid);

        return redirect()
            ->route('forms.build.edit', $form)
            ->with('message', 'Form updated.');
    }

    /**
     * Show create field (attribute) for form.
     */
    public function createField(Form $form): View
    {
        $fieldTypes = FieldType::orderBy('name')->get();
        $parentFieldsWithOptions = $this->getParentFieldsWithOptions($form, null);

        return view('forms.build.field-form', [
            'form' => $form,
            'field' => null,
            'fieldTypes' => $fieldTypes,
            'parentFieldsWithOptions' => $parentFieldsWithOptions,
        ]);
    }

    /**
     * Store new field (attribute).
     */
    public function storeField(Request $request, Form $form): RedirectResponse
    {
        $valid = $request->validate([
            'field_type_id' => 'required|exists:field_types,id',
            'name' => 'required|string|max:100|regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable',
        ]);

        $exists = $form->fields()->where('name', $valid['name'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'A field with this name already exists on this form.']);
        }

        $valid['form_id'] = $form->id;
        $valid['sort_order'] = (int) ($valid['sort_order'] ?? $form->fields()->max('sort_order') + 1);
        $valid['is_required'] = $request->boolean('is_required');
        unset($valid['conditional_logic']);

        $attr = Attribute::create($valid);

        $this->saveAttributeCondition($request, $form, $attr);

        if ($attr->fieldType->supports_options) {
            return redirect()->route('forms.build.fields.edit', [$form, $attr])
                ->with('message', 'Field added. Add options below.');
        }

        return redirect()->route('forms.build.edit', $form)
            ->with('message', 'Field added.');
    }

    /**
     * Edit field (attribute) and its options.
     */
    public function editField(Form $form, Attribute $field): View
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $field->load('fieldType', 'options', 'attributeCondition.parentAttribute');
        $fieldTypes = FieldType::orderBy('name')->get();
        $parentFieldsWithOptions = $this->getParentFieldsWithOptions($form, $field->id);

        return view('forms.build.field-form', [
            'form' => $form,
            'field' => $field,
            'fieldTypes' => $fieldTypes,
            'parentFieldsWithOptions' => $parentFieldsWithOptions,
        ]);
    }

    /**
     * Update field (attribute).
     */
    public function updateField(Request $request, Form $form, Attribute $field): RedirectResponse
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $valid = $request->validate([
            'field_type_id' => 'required|exists:field_types,id',
            'name' => 'required|string|max:100|regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable',
        ]);

        $exists = $form->fields()->where('name', $valid['name'])->where('id', '!=', $field->id)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'A field with this name already exists on this form.']);
        }

        $valid['sort_order'] = (int) ($valid['sort_order'] ?? $field->sort_order);
        $valid['is_required'] = $request->boolean('is_required');
        unset($valid['conditional_logic']);

        $field->update($valid);

        $this->saveAttributeCondition($request, $form, $field);

        return redirect()->route('forms.build.edit', $form)
            ->with('message', 'Field updated.');
    }

    /**
     * Move field up (decrease position in form).
     */
    public function moveFieldUp(Form $form, Attribute $field): RedirectResponse
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $ordered = $form->fields()->orderBy('sort_order')->orderBy('id')->pluck('id')->values()->all();
        $pos = array_search((int) $field->id, $ordered, true);
        if ($pos === false || $pos === 0) {
            return redirect()->route('forms.build.edit', $form);
        }
        // Swap with previous in list, then renumber sort_order
        $prevId = $ordered[$pos - 1];
        $ordered[$pos - 1] = (int) $field->id;
        $ordered[$pos] = $prevId;
        $this->applyFieldOrder($form, $ordered);
        return redirect()->route('forms.build.edit', $form)->with('message', 'Order updated.');
    }

    /**
     * Move field down (increase position in form).
     */
    public function moveFieldDown(Form $form, Attribute $field): RedirectResponse
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $ordered = $form->fields()->orderBy('sort_order')->orderBy('id')->pluck('id')->values()->all();
        $pos = array_search((int) $field->id, $ordered, true);
        if ($pos === false || $pos === count($ordered) - 1) {
            return redirect()->route('forms.build.edit', $form);
        }
        // Swap with next in list, then renumber sort_order
        $nextId = $ordered[$pos + 1];
        $ordered[$pos + 1] = (int) $field->id;
        $ordered[$pos] = $nextId;
        $this->applyFieldOrder($form, $ordered);
        return redirect()->route('forms.build.edit', $form)->with('message', 'Order updated.');
    }

    /**
     * Set sort_order to 0, 1, 2, ... for the given order of attribute ids.
     */
    private function applyFieldOrder(Form $form, array $orderedIds): void
    {
        foreach ($orderedIds as $sortOrder => $id) {
            Attribute::where('id', $id)->where('form_id', $form->id)->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * Delete field (attribute).
     */
    public function destroyField(Form $form, Attribute $field): RedirectResponse
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $field->delete();

        return redirect()->route('forms.build.edit', $form)
            ->with('message', 'Field removed.');
    }

    /**
     * Store or update options for a field (AJAX or form). Using simple form post.
     */
    public function updateFieldOptions(Request $request, Form $form, Attribute $field): RedirectResponse
    {
        if ($field->form_id !== $form->id) {
            abort(404);
        }
        $options = $request->input('options', []);
        if (! is_array($options)) {
            $options = [];
        }

        $field->options()->delete();
        $sort = 0;
        foreach ($options as $opt) {
            $value = trim((string) ($opt['value'] ?? ''));
            $label = trim((string) ($opt['label'] ?? ''));
            if ($value !== '' || $label !== '') {
                $field->options()->create([
                    'value' => $value ?: $label,
                    'label' => $label ?: $value,
                    'sort_order' => $sort++,
                ]);
            }
        }

        return redirect()->route('forms.build.fields.edit', [$form, $field])
            ->with('message', 'Options saved.');
    }

    /**
     * Parent fields that have options (for "Show when" dropdown + trigger value).
     */
    private function getParentFieldsWithOptions(Form $form, ?int $excludeAttributeId): array
    {
        $query = $form->fields()->with('options', 'fieldType')->orderBy('sort_order');
        if ($excludeAttributeId !== null) {
            $query->where('id', '!=', $excludeAttributeId);
        }
        $attrs = $query->get();
        $result = [];
        foreach ($attrs as $a) {
            if (! $a->fieldType->supports_options || $a->options->isEmpty()) {
                continue;
            }
            $result[] = [
                'id' => $a->id,
                'label' => $a->label,
                'options' => $a->options->map(fn ($o) => ['value' => $o->value, 'label' => $o->label])->values()->all(),
            ];
        }
        return $result;
    }

    /**
     * Save or remove attribute_condition from request (show_if_parent_id, show_if_trigger_value).
     */
    private function saveAttributeCondition(Request $request, Form $form, Attribute $attr): void
    {
        $parentId = $request->input('show_if_parent_id');
        $triggerValue = $request->input('show_if_trigger_value');

        if ($parentId === null || $parentId === '' || $triggerValue === null || (string) $triggerValue === '') {
            $attr->attributeCondition?->delete();
            return;
        }

        $parentId = (int) $parentId;
        $parent = $form->fields()->find($parentId);
        if (! $parent) {
            return;
        }

        $attr->attributeCondition()->updateOrCreate(
            [],
            [
                'parent_attribute_id' => $parentId,
                'operator' => '=',
                'trigger_value' => (string) $triggerValue,
            ]
        );
    }
}
