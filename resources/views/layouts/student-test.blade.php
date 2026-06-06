<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Тестирование') — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-bg">

    <nav class="bg-white border-b border-border sticky top-0 z-40 h-14 flex items-center">
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 flex items-center gap-3">
            <a href="{{ route('student.tests.active') }}" class="flex items-center gap-0.5 shrink-0">
                <span class="text-lg font-extrabold text-primary">Ed</span><span class="text-lg font-extrabold text-text">Code</span>
            </a>
        </div>
    </nav>

    @yield('content')

    @stack('scripts')
</body>
</html>
