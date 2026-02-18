@extends('layouts.app')

@section('title', 'Hospital forms')

@section('content')
    <h1 style="margin: 0 0 1rem;">Hospital forms</h1>
    <p style="color: #555; margin-bottom: 1rem;">Forms are linked to departments — each department has its own set of forms. Choose a department to see that department’s forms. <a href="{{ route('forms.build.index') }}">Build forms</a>.</p>

    <form action="{{ route('forms.index') }}" method="get" class="card" style="margin-bottom: 1.25rem;">
        <label for="department">Department</label>
        <select id="department" name="department" onchange="this.form.submit()">
            <option value="">— Select department —</option>
            @foreach ($departments ?? [] as $d)
                <option value="{{ $d }}" @if (($selectedDepartment ?? '') === $d) selected @endif>{{ $d }}</option>
            @endforeach
            @if ($hasOther ?? false)
                <option value="__other__" @if (($selectedDepartment ?? '') === '__other__') selected @endif>Other</option>
            @endif
        </select>
        <div class="help">Forms differ by department. Only forms for the selected department are listed below.</div>
    </form>

    @if (!$selectedDepartment)
        @if (($departments ?? collect())->isNotEmpty())
            <div class="card">
                <p style="margin: 0; color: #555;">Select a department above to see that department’s forms. Each department has its own forms.</p>
            </div>
        @endif
    @endif

    @if ($selectedDepartment && $forms->isEmpty())
        <div class="card">
            <p>No forms for this department yet. Add forms in <a href="{{ route('forms.build.index') }}">Build forms</a> and set this form’s department.</p>
        </div>
    @endif

    @if ($selectedDepartment && $forms->isNotEmpty())
        <h2 style="margin: 0 0 1rem; font-size: 1.1rem; color: #444;">
            @if ($selectedDepartment === '__other__')
                Forms (Other)
            @else
                Forms for {{ $selectedDepartment }}
            @endif
        </h2>
        @foreach ($forms as $form)
            <div class="card">
                <h2 style="margin: 0 0 .5rem; font-size: 1.1rem;">{{ $form->title }}</h2>
                @if ($form->description)
                    <p>{{ $form->description }}</p>
                @endif
                <a href="{{ route('forms.show', $form->slug) }}" class="btn">Open form</a>
            </div>
        @endforeach
    @endif
@endsection
