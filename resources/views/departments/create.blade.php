@extends('layouts.app')

@section('title', 'Add department')

@section('content')
    <p class="mb-3"><a href="{{ route('departments.index') }}" class="text-decoration-none">← Back to departments</a></p>
    <h1 class="h3 mb-4">Add department</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('departments.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. ER, Admissions, Outpatient, Lab" required>
                    @error('name')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" name="description" class="form-control" value="{{ old('description') }}" placeholder="Optional">
                    @error('description')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Add department</button>
            </form>
        </div>
    </div>
@endsection
