@extends('layouts.app')

@section('title', 'New form')

@section('content')
    <p style="margin-bottom: 1rem;"><a href="{{ route('forms.build.index') }}">← Back to build forms</a></p>
    <h1 style="margin: 0 0 1rem;">New form</h1>
    <form action="{{ route('forms.build.store') }}" method="post" class="card">
        @csrf
        <div class="field">
            <label for="slug">Slug <span class="required"></span></label>
            <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g. patient_registration" required>
            <div class="help">Unique ID for the form (letters, numbers, underscores).</div>
            @error('slug')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="title">Title <span class="required"></span></label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Patient Registration" required>
            @error('title')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="department">Department</label>
            <select id="department" name="department">
                <option value="">— Select department —</option>
                @foreach ($departments ?? [] as $d)
                    <option value="{{ $d }}" @if (old('department') === $d) selected @endif>{{ $d }}</option>
                @endforeach
            </select>
            <div class="help">Add departments under <a href="{{ route('departments.index') }}">Departments</a> first.</div>
            @error('department')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2" placeholder="Optional">{{ old('description') }}</textarea>
            @error('description')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label>
                <input type="checkbox" name="is_active" value="1" @if (old('is_active', true)) checked @endif>
                Active (show on hospital forms list)
            </label>
        </div>
        <button type="submit" class="btn">Create form</button>
    </form>
@endsection
