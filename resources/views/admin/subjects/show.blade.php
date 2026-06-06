@use(App\Enums\PartType)

@extends('layouts.admin')

@section('content')

@php
    $topicsUrl = route('admin.subjects.show', $subject) . '?tab=topics';
    $nusqasUrl = route('admin.subjects.show', $subject) . '?tab=nusqas';
    $isTopics  = $tab === 'topics';
    $partType  = $isTopics ? PartType::Topic : PartType::Nusqa;
    $parts     = $isTopics ? $topics : $nusqas;
@endphp

<div class="max-w-4xl mx-auto">
    <div class="card">

        {{-- Tabs header --}}
        <div class="flex border-b border-border -mx-6 px-6 mb-6">
            <a href="{{ $topicsUrl }}"
               class="tab-item {{ $isTopics ? 'tab-active' : '' }}">
                <x-icon name="book-open" class="w-4 h-4" />
                Тақырыптар
                <span class="tab-count {{ $isTopics ? 'tab-count-active' : '' }}">{{ $topics->count() }}</span>
            </a>
            <a href="{{ $nusqasUrl }}"
               class="tab-item {{ ! $isTopics ? 'tab-active' : '' }}">
                <x-icon name="clipboard-list" class="w-4 h-4" />
                Нұсқалар
                <span class="tab-count {{ ! $isTopics ? 'tab-count-active' : '' }}">{{ $nusqas->count() }}</span>
            </a>
        </div>

        {{-- Tab actions --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-text">{{ $partType->labelPlural() }}</h3>
            <a href="{{ route('admin.subjects.parts.create', $subject) }}?type={{ $partType->value }}"
               class="btn btn-success btn-sm">
                <x-icon name="plus" class="w-4 h-4" />
                {{ $partType->createLabel() }}
            </a>
        </div>

        {{-- Tab content --}}
        @if ($parts->isEmpty())
            <div class="text-center text-text-muted py-10 border border-dashed border-border rounded-2xl">
                {{ $partType->labelPlural() }} не добавлены.
            </div>
        @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="w-10 text-text-muted">ID</th>
                            <th>Название</th>
                            <th>Вопросов</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parts as $i => $part)
                            <tr class="cursor-pointer" onclick="window.location='{{ route('admin.subjects.parts.show', [$subject, $part]) }}'">
                                <td class="text-text-muted text-xs font-mono">{{ $part->id }}</td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-info">
                                        {{ $part->title }}
                                        <x-icon name="arrow-top-right" class="w-3.5 h-3.5 shrink-0" />
                                    </span>
                                </td>
                                <td>{{ $part->questions->count() }}</td>
                                <td onclick="event.stopPropagation()">
                                    <div class="flex items-center gap-1">
                                        <x-btn.edit :route="route('admin.subjects.parts.edit', [$subject, $part])" />
                                        <x-btn.delete :route="route('admin.subjects.parts.destroy', [$subject, $part])" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection
