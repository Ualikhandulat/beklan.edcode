@use(App\Enums\QuestionType)

@extends('layouts.admin')

@section('content')

@php $addMode = $type !== null; @endphp

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
    <div class="max-w-3xl mx-auto">
        <div class="card">
            {{-- Тип вопроса — центрированные вкладки --}}
            <div class="flex justify-center flex-wrap gap-2 pb-5 mb-5 border-b border-border">
                @foreach (QuestionType::cases() as $qt)
                    <a href="{{ $showUrl }}?type={{ $qt->url() }}"
                       class="btn btn-sm {{ $type === $qt->url() ? 'btn-primary' : 'btn-outline' }}">
                        {{ $qt->title() }}
                    </a>
                @endforeach
            </div>

            @include("admin.subjects.questions.forms.{$type}")
        </div>
    </div>
@endif

@endsection
