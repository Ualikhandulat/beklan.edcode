@props(['route'])

<a href="{{ $route }}"
   {{ $attributes->class(['btn btn-ghost btn-sm text-text-muted hover:text-info hover:bg-info-light']) }}>
    <x-icon name="pencil" class="w-4 h-4" />
</a>
