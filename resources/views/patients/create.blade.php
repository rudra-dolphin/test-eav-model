@extends('layouts.app')

@section('title', 'Add patient')

@section('content')
    <p class="mb-3"><a href="{{ route('patients.index') }}" class="text-decoration-none">← Back to patients</a></p>
    <h1 class="h3 mb-4">Add patient</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('patients.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="patient_number" class="form-label">Patient number (MRN)</label>
                    <input type="text" id="patient_number" name="patient_number" class="form-control" value="{{ old('patient_number') }}" placeholder="Optional, unique">
                    <div class="form-text">Hospital ID / medical record number. Leave empty to auto-manage.</div>
                    @error('patient_number')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select">
                        <option value="">— Select department —</option>
                        @foreach ($departments ?? [] as $d)
                            <option value="{{ $d }}" @if (old('department') === $d) selected @endif>{{ $d }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Add departments under <a href="{{ route('departments.index') }}">Departments</a>. Forms for this department will show this patient when filling.</div>
                    @error('department')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="date_of_birth" class="form-label">Date of birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')<div class="form-text text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Male" @if (old('gender') === 'Male') selected @endif>Male</option>
                            <option value="Female" @if (old('gender') === 'Female') selected @endif>Female</option>
                            <option value="Other" @if (old('gender') === 'Other') selected @endif>Other</option>
                        </select>
                        @error('gender')<div class="form-text text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="e.g. +1 234 567 8900">
                    @error('phone')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                    @error('email')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    @error('address')<div class="form-text text-danger">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Add patient</button>
            </form>
        </div>
    </div>
@endsection
