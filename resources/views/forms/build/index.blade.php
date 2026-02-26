@extends('layouts.app')

@section('title', 'Build forms')

@section('content')
    <h1 class="h3 mb-2">Build dynamic forms</h1>
    <p class="text-muted mb-4">Create and edit forms. Add forms, then add fields (text, number, date, dropdown, radio, checkbox).</p>
    <p class="mb-4">
        <a href="{{ route('forms.build.create') }}" class="btn btn-primary">+ New form</a>
        <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary">View forms</a>
    </p>
    @if ($forms->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-0">No forms yet. Click "New form" to create your first form.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Department</th>
                                <th>Fields</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($forms as $f)
                                <tr>
                                    <td>{{ $f->title }}</td>
                                    <td><code class="small">{{ $f->slug }}</code></td>
                                    <td>{{ $f->department ?? '—' }}</td>
                                    <td>{{ $f->fields_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('forms.build.edit', $f) }}" class="btn btn-sm btn-primary">Edit</a>
                                        @if ($f->is_active)
                                            <a href="{{ route('forms.show', $f->slug) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
