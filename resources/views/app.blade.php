<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Snip - Portal Template & Script Operasional</title>
    
    {{-- Workaround for Laravel Vite limitation where asset helper resolution fails when deployed inside nested directories of corporate IIS/Apache intranet webroots --}}
    @if(app()->environment('local'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
        <script type="module" src="{{ asset('build/assets/app.js') }}"></script>
    @endif

    <!-- Tailwind CDN fallback for styling robustness in legacy intranet browsers -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class=