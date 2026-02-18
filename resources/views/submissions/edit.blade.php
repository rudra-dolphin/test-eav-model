@extends('layouts.app')

@section('title', 'Edit submission')

@section('content')
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('patients.show', $patient) }}">← Back to {{ $patient->name }}</a>
    </p>

    <div class="card">
        <h1 style="margin: 0 0 0.5rem;">Edit submitted values</h1>
        <p style="margin: 0; color: #555;">
            <strong>Patient:</strong> {{ $patient->name }}
            &nbsp;|&nbsp;
            <strong>Form:</strong> {{ $form->title }}
            &nbsp;|&nbsp;
            <strong>Submitted:</strong> {{ $entity->created_at?->format('Y-m-d H:i') }}
        </p>
    </div>

    <form action="{{ route('patients.submissions.update', [$patient, $entity]) }}" method="post" class="card">
        @csrf
        @method('PUT')

        @foreach ($fields as $attr)
            @php
                $av = $valuesByAttributeId->get($attr->id);
                $type = $attr->fieldType->slug;
                $current = $av?->value;
                $currentCheckbox = [];
                if ($type === 'checkbox' && $attr->fieldType->allows_multiple && is_string($av?->value_text)) {
                    $decoded = json_decode($av->value_text, true);
                    if (is_array($decoded)) $currentCheckbox = $decoded;
                }
            @endphp

            <div class="field">
                <label for="field-{{ $attr->name }}" @if ($attr->is_required) class="required" @endif>
                    {{ $attr->label }}
                </label>

                @if ($type === 'text')
                    <input type="text" id="field-{{ $attr->name }}" name="{{ $attr->name }}"
                           value="{{ old($attr->name, $current) }}"
                           placeholder="{{ $attr->placeholder ?? '' }}">
                @elseif ($type === 'number')
                    <input type="number" id="field-{{ $attr->name }}" name="{{ $attr->name }}"
                           value="{{ old($attr->name, $current) }}">
                @elseif ($type === 'decimal')
                    <input type="number" step="any" id="field-{{ $attr->name }}" name="{{ $attr->name }}"
                           value="{{ old($attr->name, $current) }}">
                @elseif ($type === 'date')
                    <input type="date" id="field-{{ $attr->name }}" name="{{ $attr->name }}"
                           value="{{ old($attr->name, $av?->value_date?->format('Y-m-d') ?? $current) }}">
                @elseif ($type === 'dropdown')
                    <select id="field-{{ $attr->name }}" name="{{ $attr->name }}">
                        <option value="">— Select —</option>
                        @foreach ($attr->options as $opt)
                            @php $sel = old($attr->name, $current); @endphp
                            <option value="{{ $opt->value }}" @if ((string) $sel === (string) $opt->value) selected @endif>{{ $opt->label }}</option>
                        @endforeach
                    </select>
                @elseif ($type === 'radio')
                    <ul class="options-list">
                        @foreach ($attr->options as $opt)
                            @php $sel = old($attr->name, $current); @endphp
                            <li>
                                <input type="radio"
                                       id="field-{{ $attr->name }}-{{ $opt->value }}"
                                       name="{{ $attr->name }}"
                                       value="{{ $opt->value }}"
                                       @if ((string) $sel === (string) $opt->value) checked @endif>
                                <label for="field-{{ $attr->name }}-{{ $opt->value }}">{{ $opt->label }}</label>
                            </li>
                        @endforeach
                    </ul>
                @elseif ($type === 'checkbox')
                    @if ($attr->options->isNotEmpty())
                        @php
                            $selArr = old($attr->name . '_checkbox', $currentCheckbox);
                            if (!is_array($selArr)) $selArr = [];
                        @endphp
                        <ul class="options-list">
                            @foreach ($attr->options as $opt)
                                <li>
                                    <input type="checkbox"
                                           id="field-{{ $attr->name }}-{{ $opt->value }}"
                                           name="{{ $attr->name }}_checkbox[]"
                                           value="{{ $opt->value }}"
                                           @if (in_array($opt->value, $selArr)) checked @endif>
                                    <label for="field-{{ $attr->name }}-{{ $opt->value }}">{{ $opt->label }}</label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <input type="checkbox" id="field-{{ $attr->name }}" name="{{ $attr->name }}" value="1"
                               @if (old($attr->name, (bool) $current)) checked @endif>
                    @endif
                @endif

                @if ($attr->help_text)
                    <div class="help">{{ $attr->help_text }}</div>
                @endif
            </div>
        @endforeach

        <button type="submit" class="btn">Save changes</button>
    </form>
@endsection

