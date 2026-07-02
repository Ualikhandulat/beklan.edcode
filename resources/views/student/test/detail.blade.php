@extends('layouts.student-test')
@section('title', __('Разбор ответов'))

@section('content')

@push('head')
<style>
    body { background: #F4F6F9; }
</style>
@endpush

<x-test-review
    :test="$test"
    :subjects-data="$subjectsData"
    :back-url="route('student.test.result', $test)"
    :back-label="__('К результатам')"
/>

@endsection
