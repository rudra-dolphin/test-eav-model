@extends('layouts.app')

@section('title', 'New form')

@section('content')
    <p class="mb-3"><a href="{{ route('forms.build.index') }}" class="text-decoration-none">← Back to build forms</a></p>
    <h1 class="h3 mb-4">New form</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('forms.build.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="slug" class="form-label required">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="e.g. patient_registration" required>
                    <div class="form-text">Unique ID for the form (letters, numbers, underscores).</div>
                    @error('slug')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label required">Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Patient Registration" required>
                    @error('title')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select">
                        <option value="">— Select department —</option>
                        @foreach ($departments ?? [] as $d)
                            <option value="{{ $d }}" @if (old('department') === $d) selected @endif>{{ $d }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Add departments under <a href="{{ route('departments.index') }}">Departments</a> first.</div>
                    @error('department')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2" placeholder="Optional">{{ old('description') }}</textarea>
                    @error('description')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @if (old('is_active', true)) checked @endif>
                        <label class="form-check-label" for="is_active">Active (show on forms list)</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create form</button>
            </form>
        </div>
    </div>
@endsection
