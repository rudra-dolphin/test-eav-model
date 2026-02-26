<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Form Management') – {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .form-section-title { font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 0.75rem; color: var(--bs-primary); }
        .form-section-title:first-of-type { margin-top: 0; }
        .form-subsection-title { font-size: 1rem; font-weight: 600; margin: 1rem 0 0.5rem; color: #495057; }
        .field.conditional-hidden { display: none !important; }
        .options-list { list-style: none; padding: 0; margin: 0; }
        .options-list li { margin-bottom: 0.5rem; }
        .options-list label { font-weight: normal; margin-left: 0.35rem; }
        label.required::after { content: " *"; color: var(--bs-danger); }
        .dept { font-size: 0.8rem; }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('forms.index') }}">Form Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2 gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="{{ route('forms.index') }}">Forms</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('patients.index') }}">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('forms.build.index') }}">Build forms</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>

</html>
