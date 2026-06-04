<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Панель') — EdCode Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="text-lg font-extrabold text-primary">Ed</span><span class="text-lg font-extrabold text-text">Code</span>
            <span class="text-xs font-bold text-text-muted bg-gray-100 px-2 py-0.5 rounded-full ml-auto">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-section-title">Меню</p>

            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <x-icon name="home" class="w-5 h-5 shrink-0" />
                Главная
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <x-icon name="users" class="w-5 h-5 shrink-0" />
                Пользователи
            </a>

            <a href="{{ route('admin.subjects.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                <x-icon name="book-open" class="w-5 h-5 shrink-0" />
                Предметы
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="flex items-center gap-3 px-1 mb-3">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                    <span class="text-xs font-extrabold text-primary">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-text truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-text-muted">{{ auth()->user()->role->title() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm w-full justify-start gap-2">
                    <x-icon name="logout" class="w-4 h-4 shrink-0" />
                    Выйти
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="admin-wrapper">
        <header class="admin-topbar">
            <div>
                <h1 class="page-title">@yield('page-title', 'Панель администратора')</h1>
                @hasSection('page-subtitle')
                    <p class="page-subtitle">@yield('page-subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-2 text-sm text-text-muted">
                <span class="font-semibold text-text">{{ auth()->user()->name }}</span>
            </div>
        </header>

        <main class="admin-content">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            @if (session('error'))
                <x-alert type="danger" :message="session('error')" />
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
