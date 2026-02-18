@extends('layouts.app')

@section('title', 'Add department')

@section('content')
    <p style="margin-bottom: 1rem;"><a href="{{ route('departments.index') }}">← Back to departments</a></p>
    <h1 style="margin: 0 0 1rem;">Add department</h1>

    <form action="{{ route('departments.store') }}" method="post" class="card">
        @csrf
        <div class="field">
            <label for="name">Name <span class="required"></span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. ER, Admissions, Outpatient, Lab" required>
            @error('name')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="description">Description</label>
            <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="Optional">
            @error('description')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn">Add department</button>
    </form>
@endsection
