@use(App\Enums\QuestionType)

@extends('layouts.admin')

@php
    $addMode = $type !== null;
@endphp

@section('actions')
    @if (! $addMode)
        <a href="{{ $showUrl }}?type={{ QuestionType::SELECT_ONE->value }}" class="btn btn-primary">
            + Добавить вопрос
        </a>
    @endif
@endsection

@section('content')

@if (! $addMode)

    {{-- Stats bar --}}
        <div class="card p-4 mb-5 flex flex-wrap items-center gap-x-3 gap-y-2">
            <div>
                <span class="text-2xl font-extrabold text-text">{{ $questions->count() }}</span>
                <span class="text-xs font-bold text-text-muted ml-1.5 uppercase tracking-wide">вопросов</span>
            </div>
            <div class="w-px h-6 bg-border hidden sm:block"></div>
            @foreach (QuestionType::cases() as $qt)
                @php $cnt = $questions->where('type', $qt)->count(); @endphp
                @if($cnt > 0)
                    <div class="flex items-center gap-2 border rounded-2xl px-2.5 py-1 text-sm" style="border-color: {{ $qt->color() }}; color: {{ $qt->color() }}">
                        <x-icon :name="'question-' . $qt->value" class="w-3.5 h-3.5 shrink-0" />
                        <p>{{ $qt->title() }}: <b>{{ $cnt }}</b></p>
                    </div>
                @endif
            @endforeach
        </div>

    {{-- Question list --}}
    @if ($questions->isEmpty())
        <div class="card flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <x-icon name="clipboard-list" class="w-7 h-7 text-text-muted" />
            </div>
            <p class="font-bold text-text mb-1">Вопросы не добавлены</p>
            <p class="text-sm text-text-muted mb-5">Добавьте первый вопрос к этому разделу</p>
            <a href="{{ $showUrl }}?type={{ QuestionType::SELECT_ONE->value }}" class="btn btn-primary">
                + Добавить вопрос
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($questions as $i => $question)
                @include('admin.subjects.questions._question_row', [
                    'subject'      => $subject,
                    'part'         => $part,
                    'question'     => $question,
                    'index'        => $i + 1,
                    'editRoute'    => route('admin.subjects.parts.questions.edit', [$subject, $part, $question]),
                    'destroyRoute' => route('admin.subjects.parts.questions.destroy', [$subject, $part, $question]),
                ])
            @endforeach
        </div>
    @endif

@else

    {{-- Add question form --}}
    <div class="space-y-4">

        {{-- Type selector --}}
        <div class="card">
            <div class="flex flex-wrap gap-2">
                @foreach (QuestionType::cases() as $qt)
                    <a href="{{ $showUrl }}?type={{ $qt->value }}"
                       class="q-type-tab {{ $type === $qt->value ? 'q-type-tab-active' : '' }}"
                       style="--tab-color: {{ $qt->color() }}">
                        <x-icon :name="'question-' . $qt->value" class="q-type-tab-icon" />
                        {{ $qt->title() }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Form --}}
        <div class="card">
            @include("admin.subjects.questions.forms.{$type}")
        </div>

    </div>

@endif

@endsection
