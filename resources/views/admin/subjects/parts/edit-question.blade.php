@extends('layouts.admin')

@section('content')
<div class="space-y-4">

    {{-- Type indicator --}}
    <div class="card">
        <div class="flex flex-wrap gap-2">
            <span class="q-type-tab q-type-tab-active" style="--tab-color: {{ $question->type->color() }}">
                <x-icon :name="'question-' . $question->type->value" class="q-type-tab-icon" />
                {{ $question->type->title() }}
            </span>
        </div>
    </div>

    {{-- Form --}}
    <div class="card">
        @include("admin.subjects.questions.forms.{$question->type->value}", [
            'q'           => $question,
            'updateRoute' => $updateRoute,
        ])
    </div>

</div>
@endsection
