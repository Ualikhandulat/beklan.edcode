<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Кабинет') — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="public-nav">
        <div class="public-nav-inner">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-1">
                <span class="text-xl font-extrabold text-primary">Ed</span><span class="text-xl font-extrabold text-text">Code</span>
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm font-semibold text-text">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-8">
        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @yield('content')
    </main>
</body>
</html>
