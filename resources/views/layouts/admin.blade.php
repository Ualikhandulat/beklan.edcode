<!DOCTYPE html>
@php
    $navItems  = $navigations ?? [];
    $lastLabel = count($navItems) > 0 ? array_values($navItems)[count($navItems) - 1] : null;
    $pageTitle = $lastLabel
        ? $lastLabel . ' — ' . config('app.name')
        : config('app.name');
@endphp
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
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

            <a href="{{ route('admin.groups.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}">
                <x-icon name="user-group" class="w-5 h-5 shrink-0" />
                Группы
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <x-icon name="users" class="w-5 h-5 shrink-0" />
                Пользователи
            </a>

            <a href="{{ route('admin.subjects.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                <x-icon name="academic-cap" class="w-5 h-5 shrink-0" />
                Предметы
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="flex items-center gap-3 px-1 mb-3">
                <div class="w-10 h-10 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                    <span class="text-sm font-extrabold text-primary">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-text truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-text-muted">{{ auth()->user()->role->title() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm w-full justify-start gap-2 text-text-muted hover:text-danger hover:bg-danger-light">
                    <x-icon name="logout" class="w-4 h-4 shrink-0" />
                    Выйти из системы
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="admin-wrapper">
        <main class="admin-content">

            {{-- Breadcrumb --}}
            @if (count($navItems) > 0)
                <nav class="flex items-center gap-1.5 mb-5 text-sm flex-wrap">
                    <a href="{{ route('admin.dashboard') }}" class="text-text-muted hover:text-primary transition-colors shrink-0">
                        <x-icon name="home" class="w-4 h-4" />
                    </a>
                    @foreach ($navItems as $url => $label)
                        <x-icon name="chevron-right" class="w-4 h-4 text-black shrink-0" />
                        @if ($loop->last)
                            <span class="text-text font-bold truncate">{{ $label }}</span>
                        @else
                            <a href="{{ $url }}" class="text-text-muted hover:text-primary transition-colors shrink-0">
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            @endif

            {{-- Flash messages --}}
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
