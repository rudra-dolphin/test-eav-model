@extends('layouts.app')

@section('title', 'Patient: ' . $patient->name)

@section('content')
    <p style="margin-bottom: 1rem;"><a href="{{ route('patients.index') }}">← Back to patients</a></p>

    <div class="card">
        <h1 style="margin: 0 0 0.75rem;">{{ $patient->name }}</h1>
        <p style="margin: 0; color: #555;">
            <strong>Department:</strong> {{ $patient->department ?? '—' }}
            &nbsp;|&nbsp;
            <strong>Patient #:</strong> {{ $patient->patient_number ?? '—' }}
            &nbsp;|&nbsp;
            <strong>DOB:</strong> {{ $patient->date_of_birth?->format('Y-m-d') ?? '—' }}
        </p>
    </div>

    <h2 style="margin: 1.25rem 0 0.75rem; font-size: 1.1rem;">Submitted forms</h2>

    @if ($submissions->isEmpty())
        <div class="card">
            <p>No submissions yet for this patient.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                        <th style="padding: 0.5rem 0;">Form</th>
                        <th style="padding: 0.5rem 0;">Status</th>
                        <th style="padding: 0.5rem 0;">Submitted at</th>
                        <th style="padding: 0.5rem 0;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions as $s)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem 0;">{{ $s->form?->title ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $s->status }}</td>
                            <td style="padding: 0.5rem 0;">{{ $s->created_at?->format('Y-m-d H:i') }}</td>
                            <td style="padding: 0.5rem 0;">
                                <a class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.9rem;"
                                   href="{{ route('patients.submissions.edit', [$patient, $s]) }}">
                                    View / Edit values
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($submissions->hasPages())
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection

