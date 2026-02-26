@extends('layouts.app')

@section('title', $form->title)

@section('content')
    <p class="mb-3"><a href="{{ route('forms.index') }}" class="text-decoration-none">← Back to forms</a></p>
    @if ($form->department)
        <span class="badge bg-secondary dept">{{ $form->department }}</span>
    @endif
    <h1 class="h3 mb-2">{{ $form->title }}</h1>
    @if ($form->description)
        <p class="text-muted mb-4">{{ $form->description }}</p>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="form-fill" action="{{ route('forms.store', $form->slug) }}" method="post">
                @csrf
                <div class="field mb-3">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select id="patient_id" name="patient_id" class="form-select">
                        <option value="">— Select patient (optional) —</option>
                        @foreach ($patients as $p)
                            <option value="{{ $p->id }}" @if (old('patient_id') == $p->id) selected @endif>
                                {{ $p->name }}@if ($p->patient_number) ({{ $p->patient_number }})@endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Link this submission to a patient record.</div>
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
                    <div class="field mb-3 @if ($hasShowIf) conditional-field @endif @if ($hasShowIf && !$isMatch) conditional-hidden @endif"
                         @if ($hasShowIf) data-show-if-field="{{ $parentId }}" data-show-if-value="{{ $triggerValue }}" @endif>
                        <label for="field-{{ $field['id'] }}" class="form-label @if (!empty($field['required'])) required @endif">
                            {{ $field['label'] }}
                        </label>
                        @if ($field['type'] === 'text')
                            <input type="text" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-control"
                                   value="{{ old($field['id']) }}"
                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                   @if (!empty($field['validation']['maxLength'])) maxlength="{{ $field['validation']['maxLength'] }}" @endif
                                   @if (!empty($field['required'])) required @endif>
                        @elseif ($field['type'] === 'textarea')
                            <textarea id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-control" rows="{{ $field['validation']['rows'] ?? 4 }}"
                                      placeholder="{{ $field['placeholder'] ?? '' }}"
                                      @if (!empty($field['validation']['maxLength'])) maxlength="{{ $field['validation']['maxLength'] }}" @endif
                                      @if (!empty($field['required'])) required @endif>{{ old($field['id']) }}</textarea>
                        @elseif ($field['type'] === 'number')
                            <input type="number" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-control"
                                   value="{{ old($field['id']) }}"
                                   @if (isset($field['validation']['min'])) min="{{ $field['validation']['min'] }}" @endif
                                   @if (isset($field['validation']['max'])) max="{{ $field['validation']['max'] }}" @endif
                                   @if (!empty($field['required'])) required @endif>
                        @elseif ($field['type'] === 'decimal')
                            <input type="number" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-control" step="any"
                                   value="{{ old($field['id']) }}"
                                   @if (!empty($field['required'])) required @endif>
                        @elseif ($field['type'] === 'date')
                            <input type="date" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-control"
                                   value="{{ old($field['id']) }}"
                                   @if (!empty($field['required'])) required @endif>
                        @elseif ($field['type'] === 'dropdown')
                            <select id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" class="form-select" @if (!empty($field['required'])) required @endif>
                                <option value="">— Select —</option>
                                @foreach ($field['options'] ?? [] as $opt)
                                    <option value="{{ $opt['value'] }}" @if (old($field['id']) === $opt['value']) selected @endif>{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        @elseif ($field['type'] === 'radio')
                            <div class="options-list">
                                @foreach ($field['options'] ?? [] as $opt)
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="field-{{ $field['id'] }}-{{ $opt['value'] }}"
                                               name="{{ $field['id'] }}" value="{{ $opt['value'] }}"
                                               @if (old($field['id']) === $opt['value']) checked @endif>
                                        <label class="form-check-label" for="field-{{ $field['id'] }}-{{ $opt['value'] }}">{{ $opt['label'] }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($field['type'] === 'checkbox')
                            @if (!empty($field['options']))
                                <div class="options-list">
                                    @foreach ($field['options'] as $opt)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="field-{{ $field['id'] }}-{{ $opt['value'] }}"
                                                   name="{{ $field['id'] }}_checkbox[]" value="{{ $opt['value'] }}"
                                                   @if (in_array($opt['value'], (array) old($field['id'] . '_checkbox'))) checked @endif>
                                            <label class="form-check-label" for="field-{{ $field['id'] }}-{{ $opt['value'] }}">{{ $opt['label'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="field-{{ $field['id'] }}" name="{{ $field['id'] }}" value="1"
                                           @if (old($field['id'])) checked @endif>
                                </div>
                            @endif
                        @endif
                        @if (!empty($field['helpText']))
                            <div class="form-text">{{ $field['helpText'] }}</div>
                        @endif
                        @error($field['id'])
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var form = document.getElementById('form-fill') || document.querySelector('.card form');
        if (!form) return;
        var conditionalFields = form.querySelectorAll('.conditional-field');
        if (!conditionalFields.length) return;

        function getParentValue(fieldId) {
            var checkboxEscaped = fieldId + '_checkbox\\[\\]';
            var checkboxGroup = form.querySelector('[name="' + checkboxEscaped + '"]');
            if (checkboxGroup) {
                var cbs = form.querySelectorAll('[name="' + checkboxEscaped + '"]:checked');
                if (cbs.length === 0) return '';
                return Array.prototype.map.call(cbs, function (c) { return (c.value || '').trim(); }).filter(Boolean).join(',');
            }
            var input = form.querySelector('[name="' + fieldId + '"]');
            if (!input) return '';
            if (input.type === 'radio') {
                var checked = form.querySelector('[name="' + fieldId + '"]:checked');
                return checked ? (checked.value || '').trim() : '';
            }
            if (input.type === 'checkbox') return input.checked ? (input.value || '').trim() : '';
            return (input.value || '').trim();
        }

        function toggleConditionalFields() {
            conditionalFields.forEach(function (wrap) {
                var parentId = wrap.getAttribute('data-show-if-field');
                var triggerValue = (wrap.getAttribute('data-show-if-value') || '').trim();
                if (!parentId) return;
                var current = getParentValue(parentId);
                var trigger = triggerValue;
                var match = (current === trigger) || (trigger && current.split(',').map(function (v) { return v.trim(); }).indexOf(trigger) !== -1);
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
