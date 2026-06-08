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

    @include('layouts._manifest')

    @stack('head')
</head>
<body class="bg-bg">

    @php
        $currentRoute = request()->route()?->getName() ?? '';
        $navLinks = [
            ['route' => 'student.dashboard',     'label' => 'Главная',          'icon' => 'clipboard-list'],
            ['route' => 'student.tests.history', 'label' => 'История',          'icon' => 'chart-bar'],
            ['route' => 'student.info',          'label' => 'Информация',       'icon' => 'book-open'],
        ];
    @endphp

    <nav class="bg-white border-b border-border sticky top-0 z-40" x-data="{ menuOpen: false }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">

            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-0.5 shrink-0">
                <span class="text-lg font-extrabold text-primary">Ed</span><span class="text-lg font-extrabold text-text">Code</span>
            </a>

            <div class="flex items-center gap-3">
                {{-- User badge --}}
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                        <span class="text-[11px] font-extrabold text-primary leading-none">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <span class="hidden sm:block text-sm font-semibold text-text truncate max-w-40">
                        {{ auth()->user()->name }}
                    </span>
                </div>

                {{-- Hamburger button --}}
                <button @click="menuOpen = !menuOpen"
                        class="w-9 h-9 flex flex-col items-center justify-center gap-1.5 rounded-xl transition-colors hover:bg-gray-100 cursor-pointer"
                        :class="menuOpen ? 'bg-gray-100' : ''"
                        aria-label="Меню">
                    <span class="w-5 h-0.5 bg-text-muted rounded-full transition-all duration-200"
                          :class="menuOpen ? 'rotate-45 translate-y-2' : ''"></span>
                    <span class="w-5 h-0.5 bg-text-muted rounded-full transition-all duration-200"
                          :class="menuOpen ? 'opacity-0' : ''"></span>
                    <span class="w-5 h-0.5 bg-text-muted rounded-full transition-all duration-200"
                          :class="menuOpen ? '-rotate-45 -translate-y-2' : ''"></span>
                </button>
            </div>
        </div>

        {{-- Dropdown menu --}}
        <div x-cloak x-show="menuOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.outside="menuOpen = false"
             class="absolute top-full left-0 right-0 bg-white border-b border-border shadow-lg z-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-2">

                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       @click="menuOpen = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors"
                       :class="''"
                       style="{{ $currentRoute === $link['route'] ? 'background: var(--color-primary-light)' : '' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $currentRoute === $link['route'] ? 'bg-primary text-white' : 'bg-gray-100 text-text-muted' }}">
                            <x-icon name="{{ $link['icon'] }}" class="w-4 h-4" />
                        </span>
                        <span class="text-sm font-bold {{ $currentRoute === $link['route'] ? 'text-primary' : 'text-text' }}">
                            {{ $link['label'] }}
                        </span>
                        @if ($currentRoute === $link['route'])
                            <svg class="w-4 h-4 text-primary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                @endforeach

                <div class="border-t border-border mt-2 pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-3 py-3 rounded-xl transition-colors hover:bg-danger/5 text-left">
                            <span class="w-8 h-8 rounded-lg bg-danger/10 flex items-center justify-center shrink-0">
                                <x-icon name="logout" class="w-4 h-4 text-danger" />
                            </span>
                            <span class="text-sm font-bold text-danger">Выйти</span>
                        </button>
                    </form>
                </div>
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
