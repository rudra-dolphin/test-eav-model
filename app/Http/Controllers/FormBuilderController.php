<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
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

        return view('forms.build.field-form', [
            'form' => $form,
            'field' => null,
            'fieldTypes' => $fieldTypes,
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

        $attr = Attribute::create($valid);

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
        $field->load('fieldType', 'options');
        $fieldTypes = FieldType::orderBy('name')->get();

        return view('forms.build.field-form', [
            'form' => $form,
            'field' => $field,
            'fieldTypes' => $fieldTypes,
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

        $field->update($valid);

        return redirect()->route('forms.build.edit', $form)
            ->with('message', 'Field updated.');
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
}
