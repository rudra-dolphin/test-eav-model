@extends('layouts.app')

@section('title', 'Build forms')

@section('content')
    <h1 style="margin: 0 0 1rem;">Build dynamic forms</h1>
    <p style="color: #555; margin-bottom: 1.25rem;">Create and edit patient forms. Add forms, then add fields (text, number, date, dropdown, radio, checkbox).</p>
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('forms.build.create') }}" class="btn">+ New form</a>
        <a href="{{ route('forms.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">View hospital forms</a>
    </p>
    @if ($forms->isEmpty())
        <div class="card">
            <p>No forms yet. Click "New form" to create your first patient form.</p>
        </div>
    @else
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                        <th style="padding: 0.5rem 0;">Title</th>
                        <th style="padding: 0.5rem 0;">Slug</th>
                        <th style="padding: 0.5rem 0;">Department</th>
                        <th style="padding: 0.5rem 0;">Fields</th>
                        <th style="padding: 0.5rem 0;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($forms as $f)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem 0;">{{ $f->title }}</td>
                            <td style="padding: 0.5rem 0;"><code>{{ $f->slug }}</code></td>
                            <td style="padding: 0.5rem 0;">{{ $f->department ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ $f->fields_count }}</td>
                            <td style="padding: 0.5rem 0;">
                                <a href="{{ route('forms.build.edit', $f) }}" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.9rem;">Build</a>
                                @if ($f->is_active)
                                    <a href="{{ route('forms.show', $f->slug) }}" style="margin-left: 0.35rem;">View</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
