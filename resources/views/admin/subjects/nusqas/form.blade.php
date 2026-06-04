@extends('layouts.admin')
@section('title', $nusqa->exists ? 'Редактировать нұсқа' : 'Добавить нұсқа')
@section('page-title', $nusqa->exists ? 'Редактировать нұсқа' : 'Добавить нұсқа')
@section('page-subtitle', $subject->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif
            <x-form.input name="title" label="Название нұсқа" :value="$nusqa->title" :required="true" placeholder="1-нұсқа" />
            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-primary">
                    {{ $nusqa->exists ? 'Сохранить' : 'Добавить нұсқа' }}
                </button>
                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
