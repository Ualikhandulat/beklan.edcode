<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EdCode') — EdCode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Ubuntu:wght@300;400;500;700&family=Ubuntu+Mono:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts._manifest')

    <style>
        :root { --accent: #F2994A; }

        body.landing {
            margin: 0;
            background: #FBF7F1;
            color: #1C150F;
            font-family: 'Ubuntu', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        body.landing a { text-decoration: none; color: inherit; }
        body.landing ::selection { background: rgba(242, 153, 74, 0.30); }

        @keyframes lp-floatA { 0%,100% { transform: rotateY(-15deg) rotateX(8deg) translateY(0); } 50% { transform: rotateY(-12deg) rotateX(6deg) translateY(-16px); } }
        @keyframes lp-floatC { 0%,100% { transform: translateY(0) rotate(4deg); } 50% { transform: translateY(12px) rotate(4deg); } }
        @keyframes lp-floatD { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes lp-glow  { 0%,100% { opacity: .5; } 50% { opacity: .85; } }
        @keyframes lp-blink { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
        @keyframes lp-drift { 0% { transform: translate(0,0); } 50% { transform: translate(30px,-26px); } 100% { transform: translate(0,0); } }

        /* hover behaviours ported from the design's style-hover attributes */
        .lp-lift    { transition: transform .2s ease; }
        .lp-lift:hover    { transform: translateY(-2px); }
        @keyframes lp-shine { 0% { left: -60%; } 55%, 100% { left: 140%; } }
        .lp-btn-shine { position: relative; overflow: hidden; }
        .lp-btn-shine::after {
            content: '';
            position: absolute;
            top: 0; left: -60%;
            width: 45%; height: 100%;
            background: linear-gradient(105deg, transparent, rgba(255,255,255,0.5), transparent);
            transform: skewX(-20deg);
            animation: lp-shine 3.2s ease-in-out infinite;
            pointer-events: none;
        }
        .lp-arrow { transition: transform .2s ease; }
        a:hover .lp-arrow { transform: translateX(4px); }
        .lp-cta     { transition: filter .2s ease; }
        .lp-cta:hover     { filter: brightness(1.05); }
        .lp-outline { transition: border-color .2s ease; }
        .lp-outline:hover { border-color: rgba(242,153,74,0.5); }
        .lp-feature { transition: transform .2s ease, border-color .2s ease; }
        .lp-feature:hover { border-color: rgba(242,153,74,0.45); transform: translateY(-4px); }
        .lp-link    { transition: color .15s ease; }
        .lp-link:hover    { color: var(--accent, #F2994A); }
        .lp-tilt-l:hover { transform: rotateY(3deg) rotateX(1deg) !important; }
        .lp-tilt-r:hover { transform: rotateY(-3deg) rotateX(1deg) !important; }

        @media (prefers-reduced-motion: reduce) {
            body.landing [style*="animation"] { animation: none !important; }
        }
    </style>
</head>
<body class="landing">
    @yield('content')
</body>
</html>