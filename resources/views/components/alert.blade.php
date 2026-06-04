@props([
    'type'    => 'info',
    'message' => '',
])

@php
$icon = match($type) {
    'success' => 'check-circle',
    'danger'  => 'exclamation-circle',
    'warning' => 'exclamation-triangle',
    default   => 'information-circle',
};
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$type}"]) }}>
    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
    <span>{{ $message ?: $slot }}</span>
</div>
