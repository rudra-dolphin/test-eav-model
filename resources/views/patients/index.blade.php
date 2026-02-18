@extends('layouts.app')

@section('title', 'Patients')

@section('content')
    <h1 style="margin: 0 0 1rem;">Patients</h1>
    <p style="color: #555; margin-bottom: 1.25rem;">Patient list. Add a patient to link form submissions to them.</p>
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('patients.create') }}" class="btn">+ Add patient</a>
    </p>

    <form action="{{ route('patients.index') }}" method="get" class="card" style="margin-bottom: 1rem;">
        <div class="field" style="margin-bottom: 0.75rem;">
            <label for="department">Department</label>
            <select id="department" name="department" onchange="this.form.submit()">
                <option value="">All departments</option>
                @foreach ($departments ?? [] as $d)
                    <option value="{{ $d }}" @if (request('department') === $d) selected @endif>{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin-bottom: 0;">
            <label for="q">Search</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Name, patient number, email, phone" style="flex: 1;">
                <button type="submit" class="btn">Search</button>
            </div>
        </div>
    </form>

    @if ($patients->isEmpty())
        <div class="card">
            <p>No patients yet. <a href="{{ route('patients.create') }}">Add a patient</a>.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                        <th style="padding: 0.5rem 0;">Name</th>
                        <th style="padding: 0.5rem 0;">Department</th>
                        <th style="padding: 0.5rem 0;">Patient #</th>
                        <th style="padding: 0.5rem 0;">DOB</th>
                        <th style="padding: 0.5rem 0;">Gender</th>
                        <th style="padding: 0.5rem 0;">Phone</th>
                        <th style="padding: 0.5rem 0;">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patients as $p)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem 0;">
                                <a href="{{ route('patients.show', $p) }}">{{ $p->name }}</a>
                            </td>
                            <td style="padding: 0.5rem 0;">{{ $p->department ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $p->patient_number ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $p->date_of_birth?->format('Y-m-d') ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $p->gender ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $p->phone ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $p->email ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($patients->hasPages())
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
