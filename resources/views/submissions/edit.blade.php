@extends('layouts.app')

@section('title', 'Edit submission')

@section('content')
    <p class="mb-3"><a href="{{ route('patients.show', $patient) }}" class="text-decoration-none">← Back to {{ $patient->name }}</a></p>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h5 mb-2">Edit submitted values</h1>
            <p class="text-muted small mb-0">
                <strong>Patient:</strong> {{ $patient->name }}
                &nbsp;|&nbsp;
                <strong>Form:</strong> {{ $form->title }}
                &nbsp;|&nbsp;
                <strong>Submitted:</strong> {{ $entity->created_at?->format('Y-m-d H:i') }}
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="submission-edit-form" action="{{ route('patients.submissions.update', [$patient, $entity]) }}" method="post">
                @csrf
                @method('PUT')

                @foreach ($fields as $attr)
                    @php
                        $type = $attr->fieldType->slug;
                    @endphp
                    @if ($type === 'heading')
                        <div class="form-section-title" id="section-{{ $attr->name }}">{{ $attr->label }}</div>
                        @continue
                    @endif
                    @if ($type === 'heading_sub')
                        <div class="form-subsection-title" id="section-{{ $attr->name }}">{{ $attr->label }}</div>
                        @continue
                    @endif
                    @php
                        $av = $valuesByAttributeId->get($attr->id);
                        $current = $av?->value;
                        $currentCheckbox = [];
                        if ($type === 'checkbox' && $attr->fieldType->allows_multiple && is_string($av?->value_text)) {
                            $decoded = json_decode($av->value_text, true);
                            if (is_array($decoded)) $currentCheckbox = $decoded;
                        }
                        $showIf = $attr->getShowIf();
                        $hasShowIf = !empty($showIf) && !empty($showIf['field']);
                        $parentId = $hasShowIf ? ($showIf['field'] ?? '') : '';
                        $triggerValue = $hasShowIf ? ($showIf['value'] ?? '') : '';
                        $isMatch = false;
                        if ($hasShowIf && $parentId) {
                            $parentAttr = $fields->firstWhere('name', $parentId);
                            $parentSaved = $parentAttr ? $valuesByAttributeId->get($parentAttr->id)?->value : null;
                            $parentVal = old($parentId, $parentSaved);
                            $isMatch = (string)($parentVal ?? '') === (string)$triggerValue;
                        }
                    @endphp

                    <div class="field mb-3 @if ($hasShowIf) conditional-field @endif @if ($hasShowIf && !$isMatch) conditional-hidden @endif"
                         @if ($hasShowIf) data-show-if-field="{{ $parentId }}" data-show-if-value="{{ $triggerValue }}" @endif>
                        <label for="field-{{ $attr->name }}" class="form-label @if ($attr->is_required) required @endif">
                            {{ $attr->label }}
                        </label>

                        @if ($type === 'text')
                            <input type="text" id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-control"
                                   value="{{ old($attr->name, $current) }}"
                                   placeholder="{{ $attr->placeholder ?? '' }}">
                        @elseif ($type === 'textarea')
                            <textarea id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-control" rows="{{ $attr->validation_config['rows'] ?? 4 }}"
                                      placeholder="{{ $attr->placeholder ?? '' }}">{{ old($attr->name, $current) }}</textarea>
                        @elseif ($type === 'number')
                            <input type="number" id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-control"
                                   value="{{ old($attr->name, $current) }}">
                        @elseif ($type === 'decimal')
                            <input type="number" step="any" id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-control"
                                   value="{{ old($attr->name, $current) }}">
                        @elseif ($type === 'date')
                            <input type="date" id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-control"
                                   value="{{ old($attr->name, $av?->value_date?->format('Y-m-d') ?? $current) }}">
                        @elseif ($type === 'dropdown')
                            <select id="field-{{ $attr->name }}" name="{{ $attr->name }}" class="form-select">
                                <option value="">— Select —</option>
                                @foreach ($attr->options as $opt)
                                    @php $sel = old($attr->name, $current); @endphp
                                    <option value="{{ $opt->value }}" @if ((string) $sel === (string) $opt->value) selected @endif>{{ $opt->label }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'radio')
                            <div class="options-list">
                                @foreach ($attr->options as $opt)
                                    @php $sel = old($attr->name, $current); @endphp
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="field-{{ $attr->name }}-{{ $opt->value }}"
                                               name="{{ $attr->name }}" value="{{ $opt->value }}"
                                               @if ((string) $sel === (string) $opt->value) checked @endif>
                                        <label class="form-check-label" for="field-{{ $attr->name }}-{{ $opt->value }}">{{ $opt->label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($type === 'checkbox')
                            @if ($attr->options->isNotEmpty())
                                @php
                                    $selArr = old($attr->name . '_checkbox', $currentCheckbox);
                                    if (!is_array($selArr)) $selArr = [];
                                @endphp
                                <div class="options-list">
                                    @foreach ($attr->options as $opt)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="field-{{ $attr->name }}-{{ $opt->value }}"
                                                   name="{{ $attr->name }}_checkbox[]" value="{{ $opt->value }}"
                                                   @if (in_array($opt->value, $selArr)) checked @endif>
                                            <label class="form-check-label" for="field-{{ $attr->name }}-{{ $opt->value }}">{{ $opt->label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="field-{{ $attr->name }}" name="{{ $attr->name }}" value="1"
                                           @if (old($attr->name, (bool) $current)) checked @endif>
                                </div>
                            @endif
                        @endif

                        @if ($attr->help_text)
                            <div class="form-text">{{ $attr->help_text }}</div>
                        @endif
                    </div>
                @endforeach

                <hr class="my-4">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var form = document.getElementById('submission-edit-form') || document.querySelector('.card form');
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
                var match = (current === triggerValue) || (triggerValue && current.split(',').map(function (v) { return v.trim(); }).indexOf(triggerValue) !== -1);
                wrap.classList.toggle('conditional-hidden', !match);
                wrap.querySelectorAll('input, select, textarea').forEach(function (el) {
                    el.disabled = !match;
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
