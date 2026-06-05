@use(App\Enums\QuestionType)

@extends('layouts.admin')

@php $addMode = $type !== null; @endphp

@section('actions')
    @if (! $addMode)
        <a href="{{ $showUrl }}?type={{ QuestionType::SELECT_ONE->url() }}" class="btn btn-primary btn-sm">
            + Добавить вопрос
        </a>
    @else
        <a href="{{ $showUrl }}" class="btn btn-outline btn-sm">← К вопросам</a>
    @endif
@endsection

@section('content')

@if (! $addMode)
    {{-- Список вопросов --}}
    @if ($questions->isEmpty())
        <div class="card text-center text-text-muted py-12">Вопросы не добавлены.</div>
    @else
        <div class="space-y-3">
            @foreach ($questions as $i => $question)
                @include('admin.subjects.questions._question_row', [
                    'question'     => $question,
                    'index'        => $i + 1,
                    'destroyRoute' => route('admin.subjects.parts.questions.destroy', [$subject, $part, $question]),
                ])
            @endforeach
        </div>
    @endif

@else
    {{-- Форма добавления вопроса --}}
    <div class="space-y-4">
        {{-- Тип вопроса — отдельный card --}}
        <div class="card">
            <div class="flex justify-center flex-wrap gap-2">
                @foreach (QuestionType::cases() as $qt)
                    <a href="{{ $showUrl }}?type={{ $qt->url() }}"
                       class="btn btn-sm {{ $type === $qt->url() ? 'btn-primary' : 'btn-outline' }}">
                        {{ $qt->title() }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Форма --}}
        <div class="card">
            @include("admin.subjects.questions.forms.{$type}")
        </div>
    </div>
@endif

@endsection
