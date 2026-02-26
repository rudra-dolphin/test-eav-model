@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <h1 class="h3 mb-2">Departments</h1>
    <p class="text-muted mb-4">Add departments here. They will appear when building forms and adding patients.</p>
    <p class="mb-4">
        <a href="{{ route('departments.create') }}" class="btn btn-primary">+ Add department</a>
    </p>

    @if ($departments->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-0">No departments yet. <a href="{{ route('departments.create') }}">Add a department</a> (e.g. ER, Admissions, Outpatient, Lab) so you can assign them to forms and patients.</p>
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
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departments as $d)
                                <tr>
                                    <td>{{ $d->name }}</td>
                                    <td>{{ $d->description ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
