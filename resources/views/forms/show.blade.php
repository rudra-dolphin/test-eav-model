@extends('layouts.app')

@section('title', $form->title)

@section('content')
    <p style="margin-bottom: 1rem;"><a href="{{ route('forms.index') }}">← Back to hospital forms</a></p>
    @if ($form->department)
        <span class="dept">{{ $form->department }}</span>
    @endif
    <h1 style="margin: 0 0 1rem;">{{ $form->title }}</h1>
    @if ($form->description)
        <p style="color: #555; margin-bottom: 1.25rem;">{{ $form->description }}</p>
    @endif

    <div class="card">
    <form id="form-fill" action="{{ route('forms.store', $form->slug) }}" method="post">
        @csrf
        <div class="field">
            <label for="patient_id">Patient</label>
            <select id="patient_id" name="patient_id">
                <option value="">— Select patient (optional) —</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" @if (old('patient_id') == $p->id) selected @endif>
                        {{ $p->name }}@if ($p->patient_number) ({{ $p->patient_number }})@endif
                    </option>
                @endforeach
            </select>
            <div class="help">Link this submission to a patient record.</div>
        </div>
        @foreach ($structure['fields'] as $field)
            @if ($field['type'] === 'heading')
                <div class="form-section-title" id="section-{{ $field['id'] }}">{{ $field['label'] }}</div>
                @continue
            @endif
            @if ($field['type'] === 'heading_sub')
                <div class="form-subsection-title" id="section-{{ $field['id'] }}">{{ $field['label'] }}</div>
                @continue
            @endif
            @php
                $showIf = $field['showIf'] ?? null;
                $hasShowIf = !empty($showIf) && !empty($showIf['field']);
                $parentId = $hasShowIf ? ($showIf['field'] ?? '') : '';
                $triggerValue = $hasShowIf ? ($showIf['value'] ?? '') : '';
                $isMatch = false;
                if ($hasShowIf && $parentId) {
                    $parentVal = old($parentId);
                    $isMatch = (string)($parentVal ?? '') === (string)$triggerValue;
                }
            @endphp
            <div class="field @if ($hasShowIf) conditional-field @endif @if ($hasShowIf && !$isMatch) conditional-hidden @endif"
                 @if ($hasShowIf) data-show-if-field="{{ $parentId }}" data-show-if-value="{{ $triggerValue }}" @endif>
                <label for="field-{{ $field['id'] }}" @if (!empty($field['required'])) class="required" @endif>
                    {{ $field['label'] }}
                </label>
                @if ($field['type'] === 'text')
                    <input type="text" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}"
                           value="{{ old($field['id']) }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           @if (!empty($field['validation']['maxLength'])) maxlength="{{ $field['validation']['maxLength'] }}" @endif
                           @if (!empty($field['required'])) required @endif>
                @elseif ($field['type'] === 'number')
                    <input type="number" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}"
                           value="{{ old($field['id']) }}"
                           @if (isset($field['validation']['min'])) min="{{ $field['validation']['min'] }}" @endif
                           @if (isset($field['validation']['max'])) max="{{ $field['validation']['max'] }}" @endif
                           @if (!empty($field['required'])) required @endif>
                @elseif ($field['type'] === 'decimal')
                    <input type="number" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}"
                           value="{{ old($field['id']) }}" step="any"
                           @if (!empty($field['required'])) required @endif>
                @elseif ($field['type'] === 'date')
                    <input type="date" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}"
                           value="{{ old($field['id']) }}"
                           @if (!empty($field['required'])) required @endif>
                @elseif ($field['type'] === 'dropdown')
                    <select id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" @if (!empty($field['required'])) required @endif>
                        <option value="">— Select —</option>
                        @foreach ($field['options'] ?? [] as $opt)
                            <option value="{{ $opt['value'] }}" @if (old($field['id']) === $opt['value']) selected @endif>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($field['type'] === 'radio')
                    <ul class="options-list">
                        @foreach ($field['options'] ?? [] as $opt)
                            <li>
                                <input type="radio" id="field-{{ $field['id'] }}-{{ $opt['value'] }}"
                                       name="{{ $field['id'] }}" value="{{ $opt['value'] }}"
                                       @if (old($field['id']) === $opt['value']) checked @endif>
                                <label for="field-{{ $field['id'] }}-{{ $opt['value'] }}">{{ $opt['label'] }}</label>
                            </li>
                        @endforeach
                    </ul>
                @elseif ($field['type'] === 'checkbox')
                    @if (!empty($field['options']))
                        <ul class="options-list">
                            @foreach ($field['options'] as $opt)
                                <li>
                                    <input type="checkbox" id="field-{{ $field['id'] }}-{{ $opt['value'] }}"
                                           name="{{ $field['id'] }}_checkbox[]" value="{{ $opt['value'] }}"
                                           @if (in_array($opt['value'], (array) old($field['id'] . '_checkbox'))) checked @endif>
                                    <label for="field-{{ $field['id'] }}-{{ $opt['value'] }}">{{ $opt['label'] }}</label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <input type="checkbox" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" value="1"
                               @if (old($field['id'])) checked @endif>
                    @endif
                @endif
                @if (!empty($field['helpText']))
                    <div class="help">{{ $field['helpText'] }}</div>
                @endif
                @error($field['id'])
                    <div class="help" style="color: #b91c1c;">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
        <button type="submit" class="btn">Submit</button>
    </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        var form = document.getElementById('form-fill') || document.querySelector('.card form');
        if (!form) return;
        var conditionalFields = form.querySelectorAll('.conditional-field');
        if (!conditionalFields.length) return;

        function getParentValue(fieldId) {
            var input = form.querySelector('[name="' + fieldId + '"]');
            var checkboxGroup = form.querySelector('[name="' + fieldId + '_checkbox[]"]');
            if (checkboxGroup) {
                var cbs = form.querySelectorAll('[name="' + fieldId + '_checkbox[]"]:checked');
                if (cbs.length === 0) return '';
                return Array.prototype.map.call(cbs, function (c) { return c.value; }).join(',');
            }
            if (!input) return '';
            if (input.type === 'radio') {
                var checked = form.querySelector('[name="' + fieldId + '"]:checked');
                return checked ? checked.value : '';
            }
            if (input.type === 'checkbox') return input.checked ? input.value : '';
            return input.value || '';
        }

        function toggleConditionalFields() {
            conditionalFields.forEach(function (wrap) {
                var parentId = wrap.getAttribute('data-show-if-field');
                var triggerValue = wrap.getAttribute('data-show-if-value');
                if (!parentId) return;
                var current = getParentValue(parentId);
                var match = (current === triggerValue) || (triggerValue && current.split(',').indexOf(triggerValue) !== -1);
                wrap.classList.toggle('conditional-hidden', !match);
                wrap.querySelectorAll('input, select, textarea').forEach(function (el) {
                    el.disabled = !match;
                    if (el.required) el.setAttribute('data-required-override', match ? '0' : '1');
                });
            });
        }

        form.addEventListener('change', toggleConditionalFields);
        form.addEventListener('input', toggleConditionalFields);
        toggleConditionalFields();
    })();
    </script>
    @endpush
@endsection
