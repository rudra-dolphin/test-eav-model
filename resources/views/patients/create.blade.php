@extends('layouts.app')

@section('title', 'Add patient')

@section('content')
    <p style="margin-bottom: 1rem;"><a href="{{ route('patients.index') }}">← Back to patients</a></p>
    <h1 style="margin: 0 0 1rem;">Add patient</h1>

    <form action="{{ route('patients.store') }}" method="post" class="card">
        @csrf
        <div class="field">
            <label for="patient_number">Patient number (MRN)</label>
            <input type="text" id="patient_number" name="patient_number" value="{{ old('patient_number') }}" placeholder="Optional, unique">
            <div class="help">Hospital ID / medical record number. Leave empty to auto-manage.</div>
            @error('patient_number')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="name">Name <span class="required"></span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="department">Department</label>
            <select id="department" name="department">
                <option value="">— Select department —</option>
                @foreach ($departments ?? [] as $d)
                    <option value="{{ $d }}" @if (old('department') === $d) selected @endif>{{ $d }}</option>
                @endforeach
            </select>
            <div class="help">Add departments under <a href="{{ route('departments.index') }}">Departments</a>. Forms for this department will show this patient when filling.</div>
            @error('department')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="date_of_birth">Date of birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
            @error('date_of_birth')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">— Select —</option>
                <option value="Male" @if (old('gender') === 'Male') selected @endif>Male</option>
                <option value="Female" @if (old('gender') === 'Female') selected @endif>Female</option>
                <option value="Other" @if (old('gender') === 'Other') selected @endif>Other</option>
            </select>
            @error('gender')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g. +1 234 567 8900">
            @error('phone')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
            @error('address')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn">Add patient</button>
    </form>
@endsection
