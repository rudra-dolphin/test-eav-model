@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <h1 style="margin: 0 0 1rem;">Departments</h1>
    <p style="color: #555; margin-bottom: 1.25rem;">Add departments here. They will appear when building forms and adding patients.</p>
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('departments.create') }}" class="btn">+ Add department</a>
    </p>

    @if ($departments->isEmpty())
        <div class="card">
            <p>No departments yet. <a href="{{ route('departments.create') }}">Add a department</a> (e.g. ER, Admissions, Outpatient, Lab) so you can assign them to forms and patients.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                        <th style="padding: 0.5rem 0;">Name</th>
                        <th style="padding: 0.5rem 0;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $d)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem 0;">{{ $d->name }}</td>
                            <td style="padding: 0.5rem 0;">{{ $d->description ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
