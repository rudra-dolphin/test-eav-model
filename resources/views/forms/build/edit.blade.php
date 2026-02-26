@extends('layouts.app')

@section('title', 'Build: ' . $form->title)

@section('content')
    <p class="mb-3">
        <a href="{{ route('forms.build.index') }}" class="text-decoration-none">← Build forms</a>
        @if ($form->is_active)
            | <a href="{{ route('forms.show', $form->slug) }}">View form</a>
        @endif
    </p>
    <h1 class="h3 mb-4">Build: {{ $form->title }}</h1>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Form details</h2>
                    <form action="{{ route('forms.build.update', $form) }}" method="post" id="form-details-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $form->slug) }}" required>
                            @error('slug')<div class="form-text text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $form->title) }}" required>
                            @error('title')<div class="form-text text-danger">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select id="department" name="department" class="form-select">
                                <option value="">— Select department —</option>
                                @foreach ($departments ?? [] as $d)
                                    <option value="{{ $d }}" @if (old('department', $form->department) === $d) selected @endif>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $form->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @if (old('is_active', $form->is_active)) checked @endif>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update form</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-2">Fields</h2>
                    <p class="text-muted small mb-3">Add fields and headings. Use <strong>Move up</strong> / <strong>Move down</strong> to reorder.</p>
                    <p class="mb-3"><a href="{{ route('forms.build.fields.create', $form) }}" class="btn btn-primary btn-sm">+ Add field</a></p>
                    @if ($form->fields->isEmpty())
                        <p class="text-muted small mb-0">No fields yet. Click "Add field" to add the first one.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 3rem;">#</th>
                                        <th>Name</th>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Placement</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($form->fields as $index => $field)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><code class="small">{{ $field->name }}</code></td>
                                            <td>{{ $field->label }}</td>
                                            <td>{{ $field->fieldType->name }}</td>
                                            <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <form action="{{ route('forms.build.fields.moveUp', [$form, $field]) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-primary text-decoration-none" title="Move up" @if ($index === 0) disabled @endif>↑</button>
                                                </form>
                                                <form action="{{ route('forms.build.fields.moveDown', [$form, $field]) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-primary text-decoration-none" title="Move down" @if ($index === $form->fields->count() - 1) disabled @endif>↓</button>
                                                </form>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('forms.build.fields.edit', [$form, $field]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="{{ route('forms.build.fields.destroy', [$form, $field]) }}" method="post" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Remove this field?');">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4 sticky-top" style="top: 1rem;">
                <div class="card-body">
                    <h2 class="h6 mb-3 text-muted">Live preview</h2>
                    <p class="form-text small mb-3">This is how the form will look to users. You can click and try the fields; nothing is submitted.</p>
                    <div id="form-preview" class="border rounded p-3 bg-white">
                        @if ($form->department)
                            <span class="badge bg-secondary dept mb-2">{{ $form->department }}</span>
                        @endif
                        <h3 id="preview-title" class="h5 mb-2">{{ $form->title }}</h3>
                        <p id="preview-description" class="text-muted small mb-3">{{ $form->description ?: '—' }}</p>
                        @if ($form->fields->isEmpty())
                            <p class="text-muted small mb-0">No fields yet. Add fields to see them here.</p>
                        @else
                            <div class="preview-fields">
                                @foreach ($form->fields as $field)
                                    @php $type = $field->fieldType->slug; @endphp
                                    @if ($type === 'heading')
                                        <div class="form-section-title mt-3 mb-2" id="preview-section-{{ $field->id }}">{{ $field->label }}</div>
                                        @continue
                                    @endif
                                    @if ($type === 'heading_sub')
                                        <div class="form-subsection-title mt-3 mb-2" id="preview-section-{{ $field->id }}">{{ $field->label }}</div>
                                        @continue
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label small @if ($field->is_required) required @endif">{{ $field->label }}</label>
                                        @if ($type === 'text')
                                            <input type="text" class="form-control form-control-sm" placeholder="{{ $field->placeholder ?? '' }}" tabindex="-1">
                                        @elseif ($type === 'textarea')
                                            <textarea class="form-control form-control-sm" rows="2" placeholder="{{ $field->placeholder ?? '' }}" tabindex="-1"></textarea>
                                        @elseif ($type === 'number')
                                            <input type="number" class="form-control form-control-sm" tabindex="-1">
                                        @elseif ($type === 'decimal')
                                            <input type="number" step="any" class="form-control form-control-sm" tabindex="-1">
                                        @elseif ($type === 'date')
                                            <input type="date" class="form-control form-control-sm" tabindex="-1">
                                        @elseif ($type === 'dropdown')
                                            <select class="form-select form-select-sm" tabindex="-1">
                                                <option value="">— Select —</option>
                                                @foreach ($field->options as $opt)
                                                    <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($type === 'radio')
                                            @foreach ($field->options as $opt)
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" class="form-check-input" name="preview-{{ $field->id }}" tabindex="-1">
                                                    <label class="form-check-label small">{{ $opt->label }}</label>
                                                </div>
                                            @endforeach
                                        @elseif ($type === 'checkbox')
                                            @if ($field->options->isNotEmpty())
                                                @foreach ($field->options as $opt)
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" tabindex="-1">
                                                        <label class="form-check-label small">{{ $opt->label }}</label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" tabindex="-1">
                                                </div>
                                            @endif
                                        @endif
                                        @if ($field->help_text)
                                            <div class="form-text small">{{ $field->help_text }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-muted small mt-2 mb-0"><em>Preview only — no submit. The real form has a submit button.</em></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var titleEl = document.getElementById('title');
        var descEl = document.getElementById('description');
        var previewTitle = document.getElementById('preview-title');
        var previewDesc = document.getElementById('preview-description');
        if (!titleEl || !previewTitle) return;
        function updatePreview() {
            previewTitle.textContent = titleEl.value || '—';
            previewDesc.textContent = descEl.value || '—';
            if (!previewDesc.textContent) previewDesc.textContent = '—';
        }
        titleEl.addEventListener('input', updatePreview);
        titleEl.addEventListener('change', updatePreview);
        descEl.addEventListener('input', updatePreview);
        descEl.addEventListener('change', updatePreview);
    })();
    </script>
    @endpush
@endsection
