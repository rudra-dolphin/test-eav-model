<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hospital Forms') – {{ config('app.name') }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, sans-serif;
            margin: 0;
            padding: 1rem;
            background: #f5f5f0;
            color: #222;
            line-height: 1.5;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 42rem;
            margin: 0 auto;
        }

        nav {
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #ddd;
        }

        .dept {
            font-size: 0.8rem;
            color: #555;
            background: #eee;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .dept-heading {
            font-size: 0.9rem;
            color: #555;
            margin: 1.25rem 0 0.5rem;
            padding-bottom: 0.25rem;
        }

        .dept-heading:first-of-type {
            margin-top: 0;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .card h2 {
            margin: 0 0 .5rem;
            font-size: 1.1rem;
        }

        .card p {
            margin: 0;
            color: #555;
            font-size: 0.9rem;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.35rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.5rem .75rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .field {
            margin-bottom: 1.25rem;
        }

        .field .help {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .required::after {
            content: " *";
            color: #b91c1c;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #64748b;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .options-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .options-list li {
            margin-bottom: 0.5rem;
        }

        .options-list label {
            display: inline;
            font-weight: normal;
            margin-left: 0.35rem;
        }

        .field.conditional-hidden {
            display: none;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #ea580c;
            margin: 1.5rem 0 0.75rem;
        }
        .form-section-title:first-of-type {
            margin-top: 0;
        }
        .form-subsection-title {
            font-size: 1rem;
            font-weight: bold;
            color: #374151;
            margin: 1rem 0 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <a href="{{ route('forms.index') }}">Hospital forms</a>
            <a href="{{ route('departments.index') }}" style="margin-left: 1rem;">Departments</a>
            <a href="{{ route('patients.index') }}" style="margin-left: 1rem;">Patients</a>
            <a href="{{ route('forms.build.index') }}" style="margin-left: 1rem;">Build forms</a>
        </nav>
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @yield('content')
        @stack('scripts')
    </div>
</body>

</html>
