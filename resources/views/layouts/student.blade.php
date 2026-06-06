<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Кабинет') — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-bg">

    <nav class="bg-white border-b border-border sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">

            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-0.5 shrink-0">
                <span class="text-lg font-extrabold text-primary">Ed</span><span class="text-lg font-extrabold text-text">Code</span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                        <span class="text-[11px] font-extrabold text-primary leading-none">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <span class="hidden sm:block text-sm font-semibold text-text truncate max-w-48">
                        {{ auth()->user()->name }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light shrink-0">
                        <x-icon name="logout" class="w-4 h-4" />
                        <span class="hidden sm:inline">Выйти</span>
                    </button>
                </form>
            </div>

        </div>
    </nav>

    <main id="main-content" class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" />
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
