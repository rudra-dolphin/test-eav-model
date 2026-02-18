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
