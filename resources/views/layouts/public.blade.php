<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EdCode') — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts._manifest')
</head>
<body>
    <nav class="public-nav">
        <div class="public-nav-inner">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo-mark.png') }}" alt="" class="w-8 h-8 shrink-0">
                <span class="text-xl font-extrabold"><span class="text-primary">Ed</span><span class="text-text">Code</span></span>
            </a>
            <div class="flex items-center gap-3">
                <x-locale-switcher />
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('Войти') }}</a>
                @endguest
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">{{ __('Выйти') }}</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="border-t border-border mt-16 py-8">
        <div class="max-w-6xl mx-auto px-6 text-center text-sm text-text-muted">
            © {{ date('Y') }} EdCode. {{ __('Все права защищены.') }}
        </div>
    </footer>
</body>
</html>
