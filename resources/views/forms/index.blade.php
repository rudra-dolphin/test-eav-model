@extends('layouts.app')

@section('title', 'Forms')

@section('content')
    <h1 class="h3 mb-2">Forms</h1>
    <p class="text-muted mb-4">Forms are linked to departments. Choose a department to see its forms. <a href="{{ route('forms.build.index') }}">Build forms</a>.</p>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('forms.index') }}" method="get">
                <label for="department" class="form-label">Department</label>
                <select id="department" name="department" class="form-select" onchange="this.form.submit()">
                    <option value="">— Select department —</option>
                    @foreach ($departments ?? [] as $d)
                        <option value="{{ $d }}" @if (($selectedDepartment ?? '') === $d) selected @endif>{{ $d }}</option>
                    @endforeach
                    @if ($hasOther ?? false)
                        <option value="__other__" @if (($selectedDepartment ?? '') === '__other__') selected @endif>Other</option>
                    @endif
                </select>
                <div class="form-text">Forms differ by department. Only forms for the selected department are listed below.</div>
            </form>
        </div>
    </div>

    @if (!$selectedDepartment)
        @if (($departments ?? collect())->isNotEmpty())
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-0">Select a department above to see that department's forms.</p>
                </div>
            </div>
        @endif
    @endif

    @if ($selectedDepartment && $forms->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="mb-0">No forms for this department yet. Add forms in <a href="{{ route('forms.build.index') }}">Build forms</a> and set the form's department.</p>
            </div>
        </div>
    @endif

    @if ($selectedDepartment && $forms->isNotEmpty())
        <h2 class="h5 mb-3">
            @if ($selectedDepartment === '__other__')
                Forms (Other)
            @else
                Forms for {{ $selectedDepartment }}
            @endif
        </h2>
        <div class="row g-3">
            @foreach ($forms as $form)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="card-title h6">{{ $form->title }}</h3>
                            @if ($form->description)
                                <p class="card-text small text-muted">{{ $form->description }}</p>
                            @endif
                            <a href="{{ route('forms.show', $form->slug) }}" class="btn btn-primary btn-sm">Open form</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
