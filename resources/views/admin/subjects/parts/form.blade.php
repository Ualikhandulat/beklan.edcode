@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            <input type="hidden" name="type" value="{{ $part->type?->value ?? request('type', 'topic') }}">

            <x-form.input name="title" label="Название" :value="$part->title" :required="true"
                placeholder="{{ $part->type?->label() ?? 'Название' }}" />

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-success">
                    {{ $part->exists ? 'Сохранить' : ($part->type?->createLabel() ?? 'Добавить') }}
                </button>
                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
