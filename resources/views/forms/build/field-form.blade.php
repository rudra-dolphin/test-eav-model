@extends('layouts.app')

@section('title', $field ? 'Edit field' : 'Add field')

@section('content')
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('forms.build.edit', $form) }}">← Back to {{ $form->title }}</a>
    </p>
    <h1 style="margin: 0 0 1rem;">{{ $field ? 'Edit field' : 'Add field' }}</h1>

    <form action="{{ $field ? route('forms.build.fields.update', [$form, $field]) : route('forms.build.fields.store', $form) }}" method="post" class="card">
        @csrf
        @if ($field)@method('PUT')@endif
        <div class="field">
            <label for="field_type_id">Field type <span class="required"></span></label>
            <select id="field_type_id" name="field_type_id" required>
                @foreach ($fieldTypes as $ft)
                    <option value="{{ $ft->id }}" @if (old('field_type_id', $field?->field_type_id) == $ft->id) selected @endif>
                        {{ $ft->name }} ({{ $ft->slug }})@if ($ft->supports_options) — has options @endif
                    </option>
                @endforeach
            </select>
            @error('field_type_id')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="name">Name (machine key) <span class="required"></span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $field?->name) }}" placeholder="e.g. fullName" pattern="[a-zA-Z][a-zA-Z0-9_]*" required {{ $field ? 'readonly' : '' }}>
            <div class="help">Letters, numbers, underscore. Cannot change after create.</div>
            @error('name')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="label">Label <span class="required"></span></label>
            <input type="text" id="label" name="label" value="{{ old('label', $field?->label) }}" placeholder="e.g. Full Name" required>
            @error('label')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="placeholder">Placeholder</label>
            <input type="text" id="placeholder" name="placeholder" value="{{ old('placeholder', $field?->placeholder) }}">
            @error('placeholder')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="help_text">Help text</label>
            <textarea id="help_text" name="help_text" rows="2">{{ old('help_text', $field?->help_text) }}</textarea>
            @error('help_text')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="sort_order">Sort order</label>
            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $field?->sort_order ?? 0) }}" min="0">
            @error('sort_order')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label><input type="checkbox" name="is_required" value="1" @if (old('is_required', $field?->is_required)) checked @endif> Required</label>
        </div>
        {{-- Show this field when (optional) - commented out; use Sub fields on parent instead --}}
        {{--
        <div class="field" style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #eee;">
            <h3 style="margin: 0 0 0.5rem; font-size: 1rem;">Show this field when (optional)</h3>
            <p style="margin: 0 0 0.75rem; color: #555; font-size: 0.9rem;">Show this field only when another field has a specific value.</p>
            @php
                $cond = $field?->attributeCondition;
                $selectedParentId = old('show_if_parent_id', $cond?->parent_attribute_id);
                $selectedTriggerValue = old('show_if_trigger_value', $cond?->trigger_value);
            @endphp
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <div style="min-width: 180px;">
                    <label for="show_if_parent_id">Parent field</label>
                    <select id="show_if_parent_id" name="show_if_parent_id">
                        <option value="">— None (always show) —</option>
                        @foreach ($parentFieldsWithOptions ?? [] as $pf)
                            <option value="{{ $pf['id'] }}" @if ((string)$selectedParentId === (string)$pf['id']) selected @endif>{{ $pf['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width: 180px;">
                    <label for="show_if_trigger_value">Trigger value</label>
                    <select id="show_if_trigger_value" name="show_if_trigger_value" data-initial-value="{{ e($selectedTriggerValue ?? '') }}">
                        <option value="">— Select parent first —</option>
                        @foreach ($parentFieldsWithOptions ?? [] as $pf)
                            @if ((string)$selectedParentId === (string)$pf['id'])
                                @foreach ($pf['options'] as $opt)
                                    <option value="{{ e($opt['value']) }}" @if ((string)$selectedTriggerValue === (string)$opt['value']) selected @endif>{{ e($opt['label']) }}</option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            @if (empty($parentFieldsWithOptions))
                <div class="help">Add other fields with options (dropdown, radio, checkbox) to this form first, then you can set "Show when".</div>
            @endif
        </div>
        <script>
            (function () {
                var parentOptions = @json(collect($parentFieldsWithOptions ?? [])->keyBy('id')->map(fn ($p) => $p['options'])->all());
                var selParent = document.getElementById('show_if_parent_id');
                var selTrigger = document.getElementById('show_if_trigger_value');
                if (!selParent || !selTrigger) return;
                function updateTriggerOptions() {
                    var id = selParent.value;
                    var opts = parentOptions[id] || [];
                    selTrigger.innerHTML = '';
                    var first = document.createElement('option');
                    first.value = '';
                    first.textContent = opts.length ? '— Select value —' : '— Select parent first —';
                    selTrigger.appendChild(first);
                    opts.forEach(function (o) {
                        var opt = document.createElement('option');
                        opt.value = o.value;
                        opt.textContent = o.label;
                        selTrigger.appendChild(opt);
                    });
                }
                selParent.addEventListener('change', updateTriggerOptions);
                updateTriggerOptions();
                var initial = selTrigger.getAttribute('data-initial-value');
                if (initial) selTrigger.value = initial;
            })();
        </script>
        --}}

        @if ($field && $field->fieldType->supports_options)
            @php
                $ftSlug = $field->fieldType->slug;
                $parentName = $field->name;
                $existingByTrigger = $existingSubFieldsByTrigger ?? [];
                $fieldTypesForSubField = $fieldTypesForSubField ?? $fieldTypes ?? collect();
            @endphp
            <div class="field" style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #eee;">
                <h3 style="margin: 0 0 0.75rem; font-size: 1rem;">Sub fields</h3>
                <p style="margin: 0 0 0.75rem; color: #555; font-size: 0.9rem;">Add fields that appear only when this field is checked or when a specific option is selected. Parent and trigger are set automatically.</p>

                @if ($ftSlug === 'checkbox' && $field->options->isEmpty())
                    <div style="margin-bottom: 1.25rem;">
                        <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem;">Sub Fields (Shown when checked)</h4>
                        <div id="sub-fields-checked-list">
                            @php
                                $checkedList = old('sub_fields_checked', []);
                                if ($checkedList === []) {
                                    $children = $existingByTrigger['1'] ?? collect();
                                    foreach ($children as $c) {
                                        $mk = str_starts_with($c->name ?? '', $parentName . '_') ? substr($c->name, strlen($parentName) + 1) : $c->name;
                                        $checkedList[] = ['id' => $c->id, 'label' => $c->label, 'machine_key' => $mk, 'field_type_id' => $c->field_type_id, 'required' => $c->is_required];
                                    }
                                }
                            @endphp
                            @foreach ($checkedList as $i => $row)
                                <div class="sub-field-row" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;">
                                    <input type="hidden" name="sub_fields_checked[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                                    <div style="flex: 1;"><label>Label</label><input type="text" name="sub_fields_checked[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Label" required></div>
                                    <div style="flex: 0.8;"><label>Machine key</label><input type="text" name="sub_fields_checked[{{ $i }}][machine_key]" value="{{ $row['machine_key'] ?? '' }}" placeholder="e.g. year"></div>
                                    <div style="flex: 0.8;"><label>Type</label>
                                        <select name="sub_fields_checked[{{ $i }}][field_type_id]">
                                            @foreach ($fieldTypesForSubField as $ft)
                                                <option value="{{ $ft->id }}" @if (($row['field_type_id'] ?? 0) == $ft->id) selected @endif>{{ $ft->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="flex: 0 0 auto;"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_checked[{{ $i }}][required]" value="1" @if (!empty($row['required'])) checked @endif> Required</label></div>
                                    <button type="button" class="btn btn-secondary remove-sub" style="padding: 0.5rem 0.75rem;">−</button>
                                </div>
                            @endforeach
                            @if (empty($checkedList))
                                <div class="sub-field-row" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;">
                                    <input type="hidden" name="sub_fields_checked[0][id]" value="">
                                    <div style="flex: 1;"><label>Label</label><input type="text" name="sub_fields_checked[0][label]" placeholder="Label"></div>
                                    <div style="flex: 0.8;"><label>Machine key</label><input type="text" name="sub_fields_checked[0][machine_key]" placeholder="e.g. year"></div>
                                    <div style="flex: 0.8;"><label>Type</label>
                                        <select name="sub_fields_checked[0][field_type_id]">
                                            @foreach ($fieldTypesForSubField as $ft)
                                                <option value="{{ $ft->id }}" @if ($ft->slug === 'text') selected @endif>{{ $ft->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="flex: 0 0 auto;"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_checked[0][required]" value="1"> Required</label></div>
                                    <button type="button" class="btn btn-secondary remove-sub" style="padding: 0.5rem 0.75rem;">−</button>
                                </div>
                            @endif
                        </div>
                        <button type="button" id="add-sub-checked" class="btn btn-secondary" style="margin-top: 0.5rem;">+ Add Sub Field</button>
                    </div>
                @endif

                @if (($ftSlug === 'radio' || $ftSlug === 'dropdown' || ($ftSlug === 'checkbox' && $field->options->isNotEmpty())) && $field->options->isNotEmpty())
                    @foreach ($field->options as $opt)
                        @php
                            $optVal = $opt->value;
                            $optList = old('sub_fields_option.' . $optVal, []);
                            if ($optList === []) {
                                $children = $existingByTrigger[$optVal] ?? collect();
                                foreach ($children as $c) {
                                    $prefix = $parentName . '_' . $optVal . '_';
                                    $mk = str_starts_with($c->name ?? '', $prefix) ? substr($c->name, strlen($prefix)) : $c->name;
                                    $optList[] = ['id' => $c->id, 'label' => $c->label, 'machine_key' => $mk, 'field_type_id' => $c->field_type_id, 'required' => $c->is_required];
                                }
                            }
                        @endphp
                        <div style="margin-bottom: 1.25rem;">
                            <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem;">Sub Fields (Shown when “{{ e($opt->label) }}” is selected)</h4>
                            <div class="sub-fields-option-list" data-option-value="{{ e($optVal) }}">
                                @foreach ($optList as $i => $row)
                                    <div class="sub-field-row" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;">
                                        <input type="hidden" name="sub_fields_option[{{ $optVal }}][{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                                        <div style="flex: 1;"><label>Label</label><input type="text" name="sub_fields_option[{{ $optVal }}][{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Label"></div>
                                        <div style="flex: 0.8;"><label>Machine key</label><input type="text" name="sub_fields_option[{{ $optVal }}][{{ $i }}][machine_key]" value="{{ $row['machine_key'] ?? '' }}" placeholder="e.g. year"></div>
                                        <div style="flex: 0.8;"><label>Type</label>
                                            <select name="sub_fields_option[{{ $optVal }}][{{ $i }}][field_type_id]">
                                                @foreach ($fieldTypesForSubField as $ft)
                                                    <option value="{{ $ft->id }}" @if (($row['field_type_id'] ?? 0) == $ft->id) selected @endif>{{ $ft->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div style="flex: 0 0 auto;"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_option[{{ $optVal }}][{{ $i }}][required]" value="1" @if (!empty($row['required'])) checked @endif> Required</label></div>
                                        <button type="button" class="btn btn-secondary remove-sub" style="padding: 0.5rem 0.75rem;">−</button>
                                    </div>
                                @endforeach
                                @if (empty($optList))
                                    <div class="sub-field-row" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;">
                                        <input type="hidden" name="sub_fields_option[{{ $optVal }}][0][id]" value="">
                                        <div style="flex: 1;"><label>Label</label><input type="text" name="sub_fields_option[{{ $optVal }}][0][label]" placeholder="Label"></div>
                                        <div style="flex: 0.8;"><label>Machine key</label><input type="text" name="sub_fields_option[{{ $optVal }}][0][machine_key]" placeholder="e.g. year"></div>
                                        <div style="flex: 0.8;"><label>Type</label>
                                            <select name="sub_fields_option[{{ $optVal }}][0][field_type_id]">
                                                @foreach ($fieldTypesForSubField as $ft)
                                                    <option value="{{ $ft->id }}" @if ($ft->slug === 'text') selected @endif>{{ $ft->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div style="flex: 0 0 auto;"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_option[{{ $optVal }}][0][required]" value="1"> Required</label></div>
                                        <button type="button" class="btn btn-secondary remove-sub" style="padding: 0.5rem 0.75rem;">−</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="add-sub-option btn btn-secondary" style="margin-top: 0.5rem;" data-option-value="{{ e($optVal) }}">+ Add Sub Field</button>
                        </div>
                    @endforeach
                @endif

                @if ($ftSlug === 'radio' || $ftSlug === 'dropdown')
                    @if ($field->options->isEmpty())
                        <p class="help">Save options above first, then you can add sub fields per option.</p>
                    @endif
                @endif
            </div>
            <script>
            (function () {
                @php
                    $subFieldTypeOptionsHtml = '';
                    foreach ($fieldTypesForSubField ?? [] as $ft) {
                        $subFieldTypeOptionsHtml .= '<option value="' . $ft->id . '">' . e($ft->name) . '</option>';
                    }
                @endphp
                var subFieldTypesHtml = {!! json_encode($subFieldTypeOptionsHtml) !!};
                document.getElementById('add-sub-checked')?.addEventListener('click', function () {
                    var list = document.getElementById('sub-fields-checked-list');
                    if (!list) return;
                    var n = list.querySelectorAll('.sub-field-row').length;
                    var row = document.createElement('div');
                    row.className = 'sub-field-row';
                    row.style.cssText = 'display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;';
                    row.innerHTML = '<input type="hidden" name="sub_fields_checked[' + n + '][id]" value="">' +
                        '<div style="flex:1"><label>Label</label><input type="text" name="sub_fields_checked[' + n + '][label]" placeholder="Label"></div>' +
                        '<div style="flex:0.8"><label>Machine key</label><input type="text" name="sub_fields_checked[' + n + '][machine_key]" placeholder="e.g. year"></div>' +
                        '<div style="flex:0.8"><label>Type</label><select name="sub_fields_checked[' + n + '][field_type_id]">' + subFieldTypesHtml + '</select></div>' +
                        '<div style="flex:0 0 auto"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_checked[' + n + '][required]" value="1"> Required</label></div>' +
                        '<button type="button" class="btn btn-secondary remove-sub" style="padding:0.5rem 0.75rem">−</button>';
                    list.appendChild(row);
                    row.querySelector('.remove-sub').onclick = function () { row.remove(); };
                });
                document.querySelectorAll('.add-sub-option').forEach(function (btn) {
                    btn.onclick = function () {
                        var optVal = btn.getAttribute('data-option-value');
                        var list = btn.previousElementSibling;
                        if (!list || !optVal) return;
                        var n = list.querySelectorAll('.sub-field-row').length;
                        var row = document.createElement('div');
                        row.className = 'sub-field-row';
                        row.style.cssText = 'display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0.5rem;';
                        row.innerHTML = '<input type="hidden" name="sub_fields_option[' + optVal + '][' + n + '][id]" value="">' +
                            '<div style="flex:1"><label>Label</label><input type="text" name="sub_fields_option[' + optVal + '][' + n + '][label]" placeholder="Label"></div>' +
                            '<div style="flex:0.8"><label>Machine key</label><input type="text" name="sub_fields_option[' + optVal + '][' + n + '][machine_key]" placeholder="e.g. year"></div>' +
                            '<div style="flex:0.8"><label>Type</label><select name="sub_fields_option[' + optVal + '][' + n + '][field_type_id]">' + subFieldTypesHtml + '</select></div>' +
                            '<div style="flex:0 0 auto"><label>&nbsp;</label><label><input type="checkbox" name="sub_fields_option[' + optVal + '][' + n + '][required]" value="1"> Required</label></div>' +
                            '<button type="button" class="btn btn-secondary remove-sub" style="padding:0.5rem 0.75rem">−</button>';
                        list.appendChild(row);
                        row.querySelector('.remove-sub').onclick = function () { row.remove(); };
                    };
                });
                document.querySelectorAll('.remove-sub').forEach(function (b) { b.onclick = function () { b.closest('.sub-field-row')?.remove(); }; });
            })();
            </script>
        @endif

        <button type="submit" class="btn">{{ $field ? 'Update field' : 'Add field' }}</button>
    </form>

    @if ($field && $field->fieldType->supports_options)
        <div class="card" style="margin-top: 1.5rem;">
            <h2 style="margin: 0 0 0.75rem;">Options (for dropdown / radio / checkbox)</h2>
            <form action="{{ route('forms.build.fields.options', [$form, $field]) }}" method="post" id="options-form">
                @csrf
                <div id="options-list">
                    @foreach (old('options', $field->options->map(fn ($o) => ['value' => $o->value, 'label' => $o->label])->toArray()) as $i => $opt)
                        <div class="field option-row" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                            <div style="flex: 1;"><label>Value</label><input type="text" name="options[{{ $i }}][value]" value="{{ $opt['value'] ?? '' }}" placeholder="stored"></div>
                            <div style="flex: 1;"><label>Label</label><input type="text" name="options[{{ $i }}][label]" value="{{ $opt['label'] ?? '' }}" placeholder="display"></div>
                            <button type="button" class="btn btn-secondary remove-opt" style="padding: 0.5rem 0.75rem;">−</button>
                        </div>
                    @endforeach
                    @if (empty(old('options')) && $field->options->isEmpty())
                        <div class="field option-row" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                            <div style="flex: 1;"><label>Value</label><input type="text" name="options[0][value]" placeholder="e.g. yes"></div>
                            <div style="flex: 1;"><label>Label</label><input type="text" name="options[0][label]" placeholder="e.g. Yes"></div>
                            <button type="button" class="btn btn-secondary remove-opt" style="padding: 0.5rem 0.75rem;">−</button>
                        </div>
                    @endif
                </div>
                <p style="margin: 0.75rem 0 0;"><button type="button" id="add-opt" class="btn btn-secondary">+ Add option</button></p>
                <p style="margin: 0.75rem 0 0;"><button type="submit" class="btn">Save options</button></p>
            </form>
        </div>
        <script>
            document.getElementById('add-opt')?.addEventListener('click', function () {
                var list = document.getElementById('options-list');
                var n = list.querySelectorAll('.option-row').length;
                var row = document.createElement('div');
                row.className = 'field option-row';
                row.style.cssText = 'display: flex; gap: 0.5rem; align-items: flex-end;';
                row.innerHTML = '<div style="flex:1"><label>Value</label><input type="text" name="options[' + n + '][value]" placeholder="stored"></div><div style="flex:1"><label>Label</label><input type="text" name="options[' + n + '][label]" placeholder="display"></div><button type="button" class="btn btn-secondary remove-opt" style="padding:0.5rem 0.75rem">−</button>';
                list.appendChild(row);
                row.querySelector('.remove-opt').onclick = function () { row.remove(); };
            });
            document.getElementById('options-list')?.querySelectorAll('.remove-opt').forEach(function (btn) {
                btn.onclick = function () { btn.closest('.option-row')?.remove(); };
            });
        </script>
    @endif
@endsection
