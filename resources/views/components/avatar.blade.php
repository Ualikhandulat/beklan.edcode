@props(['name', 'size' => 'md', 'singleLetter' => false])
@php
    $initials   = \App\Helpers\AvatarHelper::initials($name, $singleLetter);
    $color      = \App\Helpers\AvatarHelper::color($name);
    $sizeClass  = match ($size) {
        'sm'  => 'w-8 h-8 text-[11px]',
        'lg'  => 'w-12 h-12 text-base',
        'xl'  => 'w-14 h-14 text-lg',
        default => 'w-10 h-10 text-sm',
    };
@endphp
<div {{ $attributes->class([$sizeClass, 'rounded-full flex items-center justify-center shrink-0 font-extrabold text-white']) }}
     style="background-color: {{ $color }}">
    {{ $initials }}
</div>
