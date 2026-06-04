@use(App\Enums\QuestionType)

@extends('layouts.admin')
@section('title', $topic->title)
@section('page-title', $topic->title)
@section('page-subtitle', $subject->title . ' / Тақырып')

@section('content')

@php $addMode = $type !== null; @endphp

{{-- Верхняя навигация --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-ghost btn-sm text-text-muted">
        <x-icon name="chevron-down" class="w-4 h-4 rotate-90" />
        К предмету
    </a>

    <div class="flex gap-2">
        <a href="{{ $showUrl }}?type=one"
           class="btn btn-sm {{ $addMode ? 'btn-primary' : 'btn-outline' }}">
            <x-icon name="plus" class="w-4 h-4" />
            Добавить вопрос
        </a>
    </div>
</div>

@if (! $addMode)
    {{-- Список вопросов --}}
    @if ($questions->isEmpty())
        <div class="card text-center text-text-muted py-12 text-sm">Вопросы не добавлены.</div>
    @else
        <div class="space-y-3">
            @foreach ($questions as $i => $question)
                @include('admin.subjects.questions._question_row', [
                    'question'     => $question,
                    'index'        => $i + 1,
                    'destroyRoute' => route('admin.subjects.topics.questions.destroy', [$subject, $topic, $question]),
                ])
            @endforeach
        </div>
    @endif

@else
    {{-- Выбор типа вопроса --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach (QuestionType::cases() as $qt)
            @if ($qt !== QuestionType::IS_GROUP || true)
                <a href="{{ $showUrl }}?type={{ $qt->url() }}"
                   class="btn btn-sm {{ $type === $qt->url() ? 'btn-primary' : 'btn-outline' }}">
                    {{ $qt->title() }}
                </a>
            @endif
        @endforeach
    </div>

    {{-- Форма нужного типа --}}
    <div class="card">
        @include("admin.subjects.questions.forms.{$type}")
    </div>
@endif

@endsection
