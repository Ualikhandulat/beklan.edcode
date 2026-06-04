@extends('layouts.admin')
@section('title', $subject->title)
@section('page-title', $subject->title)
@section('page-subtitle', 'Темы и нұсқалар предмета')

@section('content')

@php
    $topicsUrl = route('admin.subjects.show', $subject) . '?tab=topics';
    $nusqasUrl = route('admin.subjects.show', $subject) . '?tab=nusqas';
@endphp

{{-- Tabs --}}
<div class="flex justify-center gap-4 mb-8 card mx-auto">
    <a href="{{ $topicsUrl }}"
       class="card card-sm min-w-48 text-center transition-all hover:shadow-md {{ $tab === 'topics' ? 'border-primary bg-primary text-white' : 'hover:border-gray-300' }}">
        <p class="text-2xl font-extrabold mb-1 {{ $tab === 'topics' ? 'text-white' : 'text-primary' }}">
            {{ $subject->topics->count() }}
        </p>
        <p class="text-sm font-bold {{ $tab === 'topics' ? 'text-white/90' : 'text-text' }}">Тақырыптар</p>
    </a>

    <a href="{{ $nusqasUrl }}"
       class="card card-sm min-w-48 text-center transition-all hover:shadow-md {{ $tab === 'nusqas' ? 'border-primary bg-primary text-white' : 'hover:border-gray-300' }}">
        <p class="text-2xl font-extrabold mb-1 {{ $tab === 'nusqas' ? 'text-white' : 'text-primary' }}">
            {{ $subject->nusqas->count() }}
        </p>
        <p class="text-sm font-bold {{ $tab === 'nusqas' ? 'text-white/90' : 'text-text' }}">Нұсқалар</p>
    </a>
</div>

{{-- Content --}}
@if ($tab === 'topics')
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-extrabold text-text">Тақырыптар</h3>
        <a href="{{ route('admin.subjects.topics.create', $subject) }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="w-4 h-4" />
            Добавить тему
        </a>
    </div>

    @if ($subject->topics->isEmpty())
        <div class="card text-center text-text-muted py-10 text-sm">Темы не добавлены.</div>
    @else
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>Название</th>
                        <th>Вопросов</th>
                        <th class="w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subject->topics as $i => $topic)
                        <tr>
                            <td class="text-text-muted">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.subjects.topics.show', [$subject, $topic]) }}"
                                   class="font-semibold hover:text-primary transition-colors">
                                    {{ $topic->title }}
                                </a>
                            </td>
                            <td><span class="badge badge-success">{{ $topic->questions->count() }}</span></td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.subjects.topics.edit', [$subject, $topic]) }}"
                                       class="btn btn-ghost btn-sm text-text-muted hover:text-info hover:bg-info-light">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.subjects.topics.destroy', [$subject, $topic]) }}"
                                          onsubmit="return confirm('Удалить тему?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@else
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-extrabold text-text">Нұсқалар</h3>
        <a href="{{ route('admin.subjects.nusqas.create', $subject) }}" class="btn btn-primary btn-sm">
            <x-icon name="plus" class="w-4 h-4" />
            Добавить нұсқа
        </a>
    </div>

    @if ($subject->nusqas->isEmpty())
        <div class="card text-center text-text-muted py-10 text-sm">Нұсқалар не добавлены.</div>
    @else
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>Название</th>
                        <th>Вопросов</th>
                        <th class="w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subject->nusqas as $i => $nusqa)
                        <tr>
                            <td class="text-text-muted">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.subjects.nusqas.show', [$subject, $nusqa]) }}"
                                   class="font-semibold hover:text-primary transition-colors">
                                    {{ $nusqa->title }}
                                </a>
                            </td>
                            <td><span class="badge badge-success">{{ $nusqa->questions->count() }}</span></td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.subjects.nusqas.edit', [$subject, $nusqa]) }}"
                                       class="btn btn-ghost btn-sm text-text-muted hover:text-info hover:bg-info-light">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.subjects.nusqas.destroy', [$subject, $nusqa]) }}"
                                          onsubmit="return confirm('Удалить нұсқа?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif

@endsection
