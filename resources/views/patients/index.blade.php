@extends('layouts.app')

@section('title', 'Patients')

@section('content')
    <h1 class="h3 mb-2">Patients</h1>
    <p class="text-muted mb-4">Patient list. Add a patient to link form submissions to them.</p>
    <p class="mb-4">
        <a href="{{ route('patients.create') }}" class="btn btn-primary">+ Add patient</a>
    </p>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('patients.index') }}" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select" onchange="this.form.submit()">
                        <option value="">All departments</option>
                        @foreach ($departments ?? [] as $d)
                            <option value="{{ $d }}" @if (request('department') === $d) selected @endif>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="q" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Name, patient number, email, phone">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($patients->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-0">No patients yet. <a href="{{ route('patients.create') }}">Add a patient</a>.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Patient #</th>
                                <th>DOB</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $p)
                                <tr>
                                    <td><a href="{{ route('patients.show', $p) }}">{{ $p->name }}</a></td>
                                    <td>{{ $p->department ?? '—' }}</td>
                                    <td>{{ $p->patient_number ?? '—' }}</td>
                                    <td>{{ $p->date_of_birth?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $p->gender ?? '—' }}</td>
                                    <td>{{ $p->phone ?? '—' }}</td>
                                    <td>{{ $p->email ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($patients->hasPages())
                    <div class="card-footer">
                        {{ $patients->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
