@extends('layouts.admin')

@section('actions')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.results.index') }}"
       class="btn btn-outline btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="arrow-left" class="w-4 h-4 shrink-0" />
        Назад
    </a>
@endsection

@section('content')

@php
    $pct = $test->percent();
    $isGood = $pct !== null && $pct >= 70;
    $isMid  = $pct !== null && $pct >= 50 && $pct < 70;
    $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');
@endphp

{{-- Attempt header --}}
<div class="card card-sm flex flex-wrap items-center gap-4 mb-5">
    <div class="flex items-center gap-3 min-w-0">
        <x-avatar :name="$test->user?->name ?? '—'" size="lg" />
        <div class="min-w-0">
            <p class="font-extrabold text-text truncate">{{ $test->user?->name ?? '—' }}</p>
            <p class="text-xs text-text-muted font-mono">
                {{ $test->user?->login }}
                @if ($test->user?->group) · {{ $test->user->group->title }} @endif
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 ml-auto">
        @if ($test->access)
            <span class="badge {{ $test->access->type->badgeClass() }}">{{ $test->access->type->label() }}</span>
        @endif
        <span class="badge badge-info">Попытка #{{ $test->attempt_number }}</span>
        @if ($pct !== null)
            <span class="badge {{ $badgeClass }}">{{ $test->total_score }}/{{ $test->max_score }} ({{ $pct }}%)</span>
        @endif
        @if ($test->durationLabel())
            <span class="text-xs text-text-muted flex items-center gap-1">
                <x-icon name="clock" class="w-3.5 h-3.5" />
                {{ $test->durationLabel() }}
            </span>
        @endif
        <span class="text-xs text-text-muted">{{ $test->completed_at?->format('d.m.Y H:i') }}</span>
    </div>
</div>

{{-- Review (работа над ошибками) --}}
<x-test-review :test="$test" :subjects-data="$subjectsData" />

@endsection
