@extends('layouts.app')

@section('title', 'Patient: ' . $patient->name)

@section('content')
    <p class="mb-3"><a href="{{ route('patients.index') }}" class="text-decoration-none">← Back to patients</a></p>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h5 mb-2">{{ $patient->name }}</h1>
            <p class="text-muted small mb-0">
                <strong>Department:</strong> {{ $patient->department ?? '—' }}
                &nbsp;|&nbsp;
                <strong>Patient #:</strong> {{ $patient->patient_number ?? '—' }}
                &nbsp;|&nbsp;
                <strong>DOB:</strong> {{ $patient->date_of_birth?->format('Y-m-d') ?? '—' }}
            </p>
        </div>
    </div>

    <h2 class="h6 mb-3">Submitted forms</h2>

    @if ($submissions->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-0">No submissions yet for this patient.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Form</th>
                                <th>Status</th>
                                <th>Submitted at</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($submissions as $s)
                                <tr>
                                    <td>{{ $s->form?->title ?? '—' }}</td>
                                    <td>{{ $s->status }}</td>
                                    <td>{{ $s->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('patients.submissions.edit', [$patient, $s]) }}" class="btn btn-sm btn-outline-primary">View / Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($submissions->hasPages())
                    <div class="card-footer">
                        {{ $submissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
