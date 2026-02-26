@extends('layouts.app')

@section('title', $field ? 'Edit field' : 'Add field')

@section('content')
    <p class="mb-3"><a href="{{ route('forms.build.edit', $form) }}" class="text-decoration-none">← Back to {{ $form->title }}</a></p>
    <h1 class="h3 mb-4">{{ $field ? 'Edit field' : 'Add field' }}</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ $field ? route('forms.build.fields.update', [$form, $field]) : route('forms.build.fields.store', $form) }}" method="post">
                @csrf
                @if ($field)@method('PUT')@endif
                <div class="mb-3">
                    <label for="field_type_id" class="form-label required">Field type</label>
                    <select id="field_type_id" name="field_type_id" class="form-select" required>
                        @foreach ($fieldTypes as $ft)
                            <option value="{{ $ft->id }}" @if (old('field_type_id', $field?->field_type_id) == $ft->id) selected @endif>
                                {{ $ft->name }} ({{ $ft->slug }})@if ($ft->supports_options) — has options @endif
                            </option>
                        @endforeach
                    </select>
                    @error('field_type_id')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label required">Name (machine key)</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $field?->name) }}" placeholder="e.g. fullName" pattern="[a-zA-Z][a-zA-Z0-9_]*" required {{ $field ? 'readonly' : '' }}>
                    <div class="form-text">Letters, numbers, underscore. Cannot change after create.</div>
                    @error('name')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="label" class="form-label required">Label</label>
                    <input type="text" id="label" name="label" class="form-control" value="{{ old('label', $field?->label) }}" placeholder="e.g. Full Name" required>
                    @error('label')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="placeholder" class="form-label">Placeholder</label>
                    <input type="text" id="placeholder" name="placeholder" class="form-control" value="{{ old('placeholder', $field?->placeholder) }}">
                    @error('placeholder')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="help_text" class="form-label">Help text</label>
                    <textarea id="help_text" name="help_text" class="form-control" rows="2">{{ old('help_text', $field?->help_text) }}</textarea>
                    @error('help_text')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $field?->sort_order ?? 0) }}" min="0">
                    @error('sort_order')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_required" name="is_required" value="1" @if (old('is_required', $field?->is_required)) checked @endif>
                        <label class="form-check-label" for="is_required">Required</label>
                    </div>
                </div>
                <hr class="my-4">
                <h3 class="h6 mb-2">Show this field when (optional)</h3>
                <p class="text-muted small mb-3">Show this field only when another field has a specific value.</p>
                @php
                    $cond = $field?->attributeCondition;
                    $selectedParentId = old('show_if_parent_id', $cond?->parent_attribute_id);
                    $selectedTriggerValue = old('show_if_trigger_value', $cond?->trigger_value);
                @endphp
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="show_if_parent_id" class="form-label">Parent field</label>
                        <select id="show_if_parent_id" name="show_if_parent_id" class="form-select">
                            <option value="">— None (always show) —</option>
                            @foreach ($parentFieldsWithOptions ?? [] as $pf)
                                <option value="{{ $pf['id'] }}" @if ((string)$selectedParentId === (string)$pf['id']) selected @endif>{{ $pf['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="show_if_trigger_value" class="form-label">Trigger value</label>
                        <select id="show_if_trigger_value" name="show_if_trigger_value" class="form-select" data-initial-value="{{ e($selectedTriggerValue ?? '') }}">
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
                    <div class="form-text">Add other fields with options (dropdown, radio, checkbox) to this form first, then you can set "Show when".</div>
                @endif
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
                <hr class="my-4">
                <button type="submit" class="btn btn-primary">{{ $field ? 'Update field' : 'Add field' }}</button>
        </div>
    </div>

    @if ($field && $field->fieldType->supports_options)
        @php
            $ftSlug = $field->fieldType->slug;
            $parentName = $field->name;
            $existingByTrigger = $existingSubFieldsByTrigger ?? [];
            $fieldTypesForSubField = $fieldTypesForSubField ?? $fieldTypes ?? collect();
        @endphp
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-2">Sub fields (child fields)</h2>
                <p class="text-muted small mb-3">Add fields that appear only when this field is checked or when a specific option is selected.</p>

                @if ($ftSlug === 'checkbox' && $field->options->isEmpty())
                    <div class="mb-4">
                        <h3 class="h6 mb-2">When checked</h3>
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
                                <div class="sub-field-row row g-2 align-items-end mb-2">
                                    <div class="col"><input type="hidden" name="sub_fields_checked[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}"><label class="form-label small">Label</label><input type="text" name="sub_fields_checked[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $row['label'] ?? '' }}" placeholder="Label"></div>
                                    <div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_checked[{{ $i }}][machine_key]" class="form-control form-control-sm" value="{{ $row['machine_key'] ?? '' }}" placeholder="e.g. details"></div>
                                    <div class="col"><label class="form-label small">Type</label><select name="sub_fields_checked[{{ $i }}][field_type_id]" class="form-select form-select-sm">@foreach ($fieldTypesForSubField as $ft)<option value="{{ $ft->id }}" @if (($row['field_type_id'] ?? 0) == $ft->id) selected @endif>{{ $ft->name }}</option>@endforeach</select></div>
                                    <div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_checked[{{ $i }}][required]" value="1" @if (!empty($row['required'])) checked @endif><label class="form-check-label small">Required</label></div></div>
                                    <div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>
                                </div>
                            @endforeach
                            @if (empty($checkedList))
                                <div class="sub-field-row row g-2 align-items-end mb-2">
                                    <div class="col"><input type="hidden" name="sub_fields_checked[0][id]" value=""><label class="form-label small">Label</label><input type="text" name="sub_fields_checked[0][label]" class="form-control form-control-sm" placeholder="Label"></div>
                                    <div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_checked[0][machine_key]" class="form-control form-control-sm" placeholder="e.g. details"></div>
                                    <div class="col"><label class="form-label small">Type</label><select name="sub_fields_checked[0][field_type_id]" class="form-select form-select-sm">@foreach ($fieldTypesForSubField as $ft)<option value="{{ $ft->id }}" @if ($ft->slug === 'text') selected @endif>{{ $ft->name }}</option>@endforeach</select></div>
                                    <div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_checked[0][required]" value="1"><label class="form-check-label small">Required</label></div></div>
                                    <div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>
                                </div>
                            @endif
                        </div>
                        <button type="button" id="add-sub-checked" class="btn btn-outline-secondary btn-sm">+ Add sub field</button>
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
                        <div class="mb-4">
                            <h3 class="h6 mb-2">When “{{ e($opt->label) }}” is selected</h3>
                            <div class="sub-fields-option-list" data-option-value="{{ e($optVal) }}">
                                @foreach ($optList as $i => $row)
                                    <div class="sub-field-row row g-2 align-items-end mb-2">
                                        <div class="col"><input type="hidden" name="sub_fields_option[{{ $optVal }}][{{ $i }}][id]" value="{{ $row['id'] ?? '' }}"><label class="form-label small">Label</label><input type="text" name="sub_fields_option[{{ $optVal }}][{{ $i }}][label]" class="form-control form-control-sm" value="{{ $row['label'] ?? '' }}" placeholder="Label"></div>
                                        <div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_option[{{ $optVal }}][{{ $i }}][machine_key]" class="form-control form-control-sm" value="{{ $row['machine_key'] ?? '' }}" placeholder="e.g. details"></div>
                                        <div class="col"><label class="form-label small">Type</label><select name="sub_fields_option[{{ $optVal }}][{{ $i }}][field_type_id]" class="form-select form-select-sm">@foreach ($fieldTypesForSubField as $ft)<option value="{{ $ft->id }}" @if (($row['field_type_id'] ?? 0) == $ft->id) selected @endif>{{ $ft->name }}</option>@endforeach</select></div>
                                        <div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_option[{{ $optVal }}][{{ $i }}][required]" value="1" @if (!empty($row['required'])) checked @endif><label class="form-check-label small">Required</label></div></div>
                                        <div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>
                                    </div>
                                @endforeach
                                @if (empty($optList))
                                    <div class="sub-field-row row g-2 align-items-end mb-2">
                                        <div class="col"><input type="hidden" name="sub_fields_option[{{ $optVal }}][0][id]" value=""><label class="form-label small">Label</label><input type="text" name="sub_fields_option[{{ $optVal }}][0][label]" class="form-control form-control-sm" placeholder="Label"></div>
                                        <div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_option[{{ $optVal }}][0][machine_key]" class="form-control form-control-sm" placeholder="e.g. details"></div>
                                        <div class="col"><label class="form-label small">Type</label><select name="sub_fields_option[{{ $optVal }}][0][field_type_id]" class="form-select form-select-sm">@foreach ($fieldTypesForSubField as $ft)<option value="{{ $ft->id }}" @if ($ft->slug === 'text') selected @endif>{{ $ft->name }}</option>@endforeach</select></div>
                                        <div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_option[{{ $optVal }}][0][required]" value="1"><label class="form-check-label small">Required</label></div></div>
                                        <div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="add-sub-option btn btn-outline-secondary btn-sm" data-option-value="{{ e($optVal) }}">+ Add sub field</button>
                        </div>
                    @endforeach
                @endif

                @if ($ftSlug === 'radio' || $ftSlug === 'dropdown')
                    @if ($field->options->isEmpty())
                        <p class="form-text small mb-0">Save options below first, then you can add sub fields per option.</p>
                    @endif
                @endif
            </div>
        </div>
        <script>
        (function () {
            var subFieldTypesHtml = @json(collect($fieldTypesForSubField ?? [])->map(fn ($ft) => ['id' => $ft->id, 'name' => $ft->name])->values()->all());
            function optionHtml(id, name) { return '<option value="' + id + '">' + (name || '') + '</option>'; }
            document.getElementById('add-sub-checked')?.addEventListener('click', function () {
                var list = document.getElementById('sub-fields-checked-list');
                if (!list) return;
                var n = list.querySelectorAll('.sub-field-row').length;
                var opts = subFieldTypesHtml.map(function (ft) { return optionHtml(ft.id, ft.name); }).join('');
                var row = document.createElement('div');
                row.className = 'sub-field-row row g-2 align-items-end mb-2';
                row.innerHTML = '<div class="col"><input type="hidden" name="sub_fields_checked[' + n + '][id]" value=""><label class="form-label small">Label</label><input type="text" name="sub_fields_checked[' + n + '][label]" class="form-control form-control-sm" placeholder="Label"></div><div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_checked[' + n + '][machine_key]" class="form-control form-control-sm" placeholder="e.g. details"></div><div class="col"><label class="form-label small">Type</label><select name="sub_fields_checked[' + n + '][field_type_id]" class="form-select form-select-sm">' + opts + '</select></div><div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_checked[' + n + '][required]" value="1"><label class="form-check-label small">Required</label></div></div><div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>';
                list.appendChild(row);
                row.querySelector('.remove-sub').onclick = function () { row.remove(); };
            });
            document.querySelectorAll('.add-sub-option').forEach(function (btn) {
                btn.onclick = function () {
                    var optVal = btn.getAttribute('data-option-value');
                    var list = btn.previousElementSibling;
                    if (!list || !optVal) return;
                    var n = list.querySelectorAll('.sub-field-row').length;
                    var opts = subFieldTypesHtml.map(function (ft) { return optionHtml(ft.id, ft.name); }).join('');
                    var row = document.createElement('div');
                    row.className = 'sub-field-row row g-2 align-items-end mb-2';
                    row.innerHTML = '<div class="col"><input type="hidden" name="sub_fields_option[' + optVal + '][' + n + '][id]" value=""><label class="form-label small">Label</label><input type="text" name="sub_fields_option[' + optVal + '][' + n + '][label]" class="form-control form-control-sm" placeholder="Label"></div><div class="col"><label class="form-label small">Machine key</label><input type="text" name="sub_fields_option[' + optVal + '][' + n + '][machine_key]" class="form-control form-control-sm" placeholder="e.g. details"></div><div class="col"><label class="form-label small">Type</label><select name="sub_fields_option[' + optVal + '][' + n + '][field_type_id]" class="form-select form-select-sm">' + opts + '</select></div><div class="col-auto"><div class="form-check mb-0"><input type="checkbox" class="form-check-input" name="sub_fields_option[' + optVal + '][' + n + '][required]" value="1"><label class="form-check-label small">Required</label></div></div><div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-sub">−</button></div>';
                    list.appendChild(row);
                    row.querySelector('.remove-sub').onclick = function () { row.remove(); };
                };
            });
            document.querySelectorAll('.remove-sub').forEach(function (b) { b.onclick = function () { b.closest('.sub-field-row')?.remove(); }; });
        })();
        </script>
    @endif

    </form>

    @if ($field && $field->fieldType->supports_options)
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Options (for dropdown / radio / checkbox)</h2>
                <form action="{{ route('forms.build.fields.options', [$form, $field]) }}" method="post" id="options-form">
                    @csrf
                    <div id="options-list">
                        @foreach (old('options', $field->options->map(fn ($o) => ['value' => $o->value, 'label' => $o->label])->toArray()) as $i => $opt)
                            <div class="option-row row g-2 align-items-end mb-2">
                                <div class="col"><label class="form-label small">Value</label><input type="text" name="options[{{ $i }}][value]" class="form-control form-control-sm" value="{{ $opt['value'] ?? '' }}" placeholder="stored"></div>
                                <div class="col"><label class="form-label small">Label</label><input type="text" name="options[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $opt['label'] ?? '' }}" placeholder="display"></div>
                                <div class="col-auto"><button type="button" class="btn btn-outline-secondary btn-sm remove-opt">−</button></div>
                            </div>
                        @endforeach
                        @if (empty(old('options')) && $field->options->isEmpty())
                            <div class="option-row row g-2 align-items-end mb-2">
                                <div class="col"><label class="form-label small">Value</label><input type="text" name="options[0][value]" class="form-control form-control-sm" placeholder="e.g. yes"></div>
                                <div class="col"><label class="form-label small">Label</label><input type="text" name="options[0][label]" class="form-control form-control-sm" placeholder="e.g. Yes"></div>
                                <div class="col-auto"><button type="button" class="btn btn-outline-secondary btn-sm remove-opt">−</button></div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3">
                        <button type="button" id="add-opt" class="btn btn-outline-secondary btn-sm">+ Add option</button>
                        <button type="submit" class="btn btn-primary btn-sm ms-2">Save options</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            document.getElementById('add-opt')?.addEventListener('click', function () {
                var list = document.getElementById('options-list');
                var n = list.querySelectorAll('.option-row').length;
                var row = document.createElement('div');
                row.className = 'option-row row g-2 align-items-end mb-2';
                row.innerHTML = '<div class="col"><label class="form-label small">Value</label><input type="text" name="options[' + n + '][value]" class="form-control form-control-sm" placeholder="stored"></div><div class="col"><label class="form-label small">Label</label><input type="text" name="options[' + n + '][label]" class="form-control form-control-sm" placeholder="display"></div><div class="col-auto"><button type="button" class="btn btn-outline-secondary btn-sm remove-opt">−</button></div>';
                list.appendChild(row);
                row.querySelector('.remove-opt').onclick = function () { row.remove(); };
            });
            document.getElementById('options-list')?.querySelectorAll('.remove-opt').forEach(function (btn) {
                btn.onclick = function () { btn.closest('.option-row')?.remove(); };
            });
        </script>
    @endif
@endsection
