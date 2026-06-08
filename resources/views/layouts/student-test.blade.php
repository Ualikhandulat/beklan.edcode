<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Тестирование')) — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts._manifest')

    @stack('head')
</head>
<body class="bg-bg">

    <nav class="bg-white border-b border-border sticky top-0 z-40 h-14 flex items-center">
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 flex items-center gap-3">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-1.5 shrink-0">
                <img src="{{ asset('images/logo-mark.png') }}" alt="" class="w-8 h-8 shrink-0">
                <span class="text-lg font-extrabold"><span class="text-primary">Ed</span><span class="text-text">Code</span></span>
            </a>
            @stack('nav-right')
        </div>
    </nav>

    @yield('content')

    @stack('scripts')
</body>
</html>
