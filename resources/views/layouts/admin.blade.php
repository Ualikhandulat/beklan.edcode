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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ nav: false }">

    {{-- Mobile topbar ─────────────────────────────────────────────────── --}}
    <div class="lg:hidden sticky top-0 z-20 h-14 bg-white border-b border-border flex items-center px-4 gap-3 shrink-0">
        <button type="button" @click="nav = !nav"
                class="w-9 h-9 flex flex-col items-center justify-center gap-1.5 rounded-xl hover:bg-gray-100 transition-colors shrink-0">
            <span :class="nav ? 'rotate-45 translate-y-2' : ''"
                  class="block w-5 h-0.5 bg-text rounded-full transition-all duration-300 origin-center"></span>
            <span :class="nav ? 'opacity-0' : 'opacity-100'"
                  class="block w-5 h-0.5 bg-text rounded-full transition-all duration-300"></span>
            <span :class="nav ? '-rotate-45 -translate-y-2' : ''"
                  class="block w-5 h-0.5 bg-text rounded-full transition-all duration-300 origin-center"></span>
        </button>
        <span class="font-extrabold text-base">
            <span class="text-primary">Ed</span><span class="text-text">Code</span>
        </span>
        <span class="text-[10px] font-bold text-text-muted bg-gray-100 px-2 py-0.5 rounded-full">Admin</span>
    </div>

    {{-- Overlay ────────────────────────────────────────────────────────── --}}
    <div x-show="nav"
         x-cloak
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="nav = false"
         class="lg:hidden fixed inset-0 bg-black/40 z-30"></div>

    {{-- Sidebar ─────────────────────────────────────────────────────────── --}}
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="sidebar">
        <div class="sidebar-brand">
            <span class="text-lg font-extrabold text-primary">Ed</span><span class="text-lg font-extrabold text-text">Code</span>
            <span class="text-xs font-bold text-text-muted bg-gray-100 px-2 py-0.5 rounded-full ml-auto">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-section-title">Меню</p>

            <a href="{{ route('admin.dashboard') }}" @click="nav = false"
               class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <x-icon name="home" class="w-5 h-5 shrink-0" />
                Главная
            </a>

            <a href="{{ route('admin.groups.index') }}" @click="nav = false"
               class="sidebar-nav-link {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}">
                <x-icon name="user-group" class="w-5 h-5 shrink-0" />
                Группы
            </a>

            <a href="{{ route('admin.users.index') }}" @click="nav = false"
               class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <x-icon name="users" class="w-5 h-5 shrink-0" />
                Пользователи
            </a>

            <a href="{{ route('admin.subjects.index') }}" @click="nav = false"
               class="sidebar-nav-link {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                <x-icon name="academic-cap" class="w-5 h-5 shrink-0" />
                Предметы
            </a>

            <a href="{{ route('admin.test-accesses.index') }}" @click="nav = false"
               class="sidebar-nav-link {{ request()->routeIs('admin.test-accesses*') ? 'active' : '' }}">
                <x-icon name="shield-check" class="w-5 h-5 shrink-0" />
                Доступы
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

    {{-- Main ───────────────────────────────────────────────────────────── --}}
    <div class="admin-wrapper">
        <main class="admin-content">

            {{-- Breadcrumb --}}
            @if (count($navItems) > 0)
                <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                    <nav class="flex items-center gap-1 flex-wrap">
                        <a href="{{ route('admin.dashboard') }}"
                           class="btn btn-outline btn-sm shrink-0">
                            <x-icon name="home" class="w-3.5 h-3.5" />
                        </a>
                        @foreach ($navItems as $url => $label)
                            <x-icon name="chevron-right" class="w-4 h-4 text-text-muted shrink-0" />
                            @if ($loop->last)
                                <span class="btn btn-sm bg-gray-100 text-text border border-border cursor-default font-bold truncate">{{ $label }}</span>
                            @else
                                <a href="{{ $url }}" class="btn btn-outline btn-sm shrink-0">{{ $label }}</a>
                            @endif
                        @endforeach
                    </nav>
                    @hasSection('actions')
                        <div class="flex items-center gap-2 shrink-0">
                            @yield('actions')
                        </div>
                    @endif
                </div>
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
