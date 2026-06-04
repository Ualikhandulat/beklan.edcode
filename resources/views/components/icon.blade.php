@props(['name'])
@php
    $path = public_path("images/icons/{$name}.svg");
    $svg  = file_exists($path) ? file_get_contents($path) : '';
    $svg  = preg_replace('/<svg\b/', '<svg ' . $attributes->toHtml(), $svg, 1);
@endphp
{!! $svg !!}
