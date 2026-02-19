@extends('layouts.app')

@section('title', 'Build: ' . $form->title)

@section('content')
    <p style="margin-bottom: 1rem;">
        <a href="{{ route('forms.build.index') }}">← Build forms</a>
        @if ($form->is_active)
            | <a href="{{ route('forms.show', $form->slug) }}">View form</a>
        @endif
    </p>
    <h1 style="margin: 0 0 1rem;">Build: {{ $form->title }}</h1>

    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.75rem;">Form details</h2>
        <form action="{{ route('forms.build.update', $form) }}" method="post" style="margin-bottom: 0;">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $form->slug) }}" required>
                @error('slug')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $form->title) }}" required>
                @error('title')<div class="help" style="color: #b91c1c;">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="department">Department</label>
                <select id="department" name="department">
                    <option value="">— Select department —</option>
                    @foreach ($departments ?? [] as $d)
                        <option value="{{ $d }}" @if (old('department', $form->department) === $d) selected @endif>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2">{{ old('description', $form->description) }}</textarea>
            </div>
            <div class="field">
                <label><input type="checkbox" name="is_active" value="1" @if (old('is_active', $form->is_active)) checked @endif> Active</label>
            </div>
            <button type="submit" class="btn">Update form</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin: 0 0 0.75rem;">Fields</h2>
        <p style="margin: 0 0 1rem; color: #555;">Add fields and headings. Use <strong>Move up</strong> / <strong>Move down</strong> to place titles and fields in the right order on the form.</p>
        <p style="margin-bottom: 1rem;"><a href="{{ route('forms.build.fields.create', $form) }}" class="btn">+ Add field</a></p>
        @if ($form->fields->isEmpty())
            <p style="color: #666;">No fields yet. Click "Add field" to add the first one.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid #ddd;">
                        <th style="padding: 0.5rem 0; width: 4rem;">#</th>
                        <th style="padding: 0.5rem 0;">Name</th>
                        <th style="padding: 0.5rem 0;">Label</th>
                        <th style="padding: 0.5rem 0;">Type</th>
                        <th style="padding: 0.5rem 0;">Required</th>
                        <th style="padding: 0.5rem 0;">Placement</th>
                        <th style="padding: 0.5rem 0;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($form->fields as $index => $field)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.5rem 0;">{{ $index + 1 }}</td>
                            <td style="padding: 0.5rem 0;"><code>{{ $field->name }}</code></td>
                            <td style="padding: 0.5rem 0;">{{ $field->label }}</td>
                            <td style="padding: 0.5rem 0;">{{ $field->fieldType->name }}</td>
                            <td style="padding: 0.5rem 0;">{{ $field->is_required ? 'Yes' : 'No' }}</td>
                            <td style="padding: 0.5rem 0;">
                                <form action="{{ route('forms.build.fields.moveUp', [$form, $field]) }}" method="post" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: #2563eb; cursor: pointer; font-size: 0.85rem; padding: 0.2rem 0.4rem;" title="Move earlier on form" @if ($index === 0) disabled @endif>↑ Up</button>
                                </form>
                                <form action="{{ route('forms.build.fields.moveDown', [$form, $field]) }}" method="post" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: #2563eb; cursor: pointer; font-size: 0.85rem; padding: 0.2rem 0.4rem;" title="Move later on form" @if ($index === $form->fields->count() - 1) disabled @endif>↓ Down</button>
                                </form>
                            </td>
                            <td style="padding: 0.5rem 0;">
                                <a href="{{ route('forms.build.fields.edit', [$form, $field]) }}" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.9rem;">Edit</a>
                                <form action="{{ route('forms.build.fields.destroy', [$form, $field]) }}" method="post" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #b91c1c; cursor: pointer; font-size: 0.9rem; padding: 0.25rem 0.5rem;" onclick="return confirm('Remove this field?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
