@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.test-accesses.create') }}" class="btn btn-success btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Выдать доступ
    </a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" action="{{ route('admin.test-accesses.index') }}" class="flex flex-wrap items-center gap-2 sm:gap-3 mb-5">

    <div class="relative w-full sm:w-64 sm:shrink-0">
        <x-icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
        <input type="search" name="search" value="{{ request('search') }}"
               placeholder="Студент или группа..."
               class="pl-10">
    </div>

    <div class="flex-1 sm:flex-none sm:w-40">
        <select name="type" onchange="this.form.submit()">
            <option value="">Все типы</option>
            @foreach (\App\Enums\TestAccessType::cases() as $t)
                <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 sm:flex-none sm:w-48">
        <select name="target" onchange="this.form.submit()">
            <option value="">Студент и группы</option>
            <option value="user"  @selected(request('target') === 'user')>Только студенты</option>
            <option value="group" @selected(request('target') === 'group')>Только группы</option>
        </select>
    </div>

    @if (request()->hasAny(['search', 'type', 'target']))
        <a href="{{ route('admin.test-accesses.index') }}"
           class="btn btn-ghost btn-sm shrink-0 text-text-muted hover:text-danger hover:bg-danger-light">
            <x-icon name="x" class="w-4 h-4" />
        </a>
    @endif

</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-12 text-text-muted hidden sm:table-cell">ID</th>
                <th>Кому</th>
                <th class="w-24 text-center">Активный</th>
                <th class="w-28">Тип</th>
                <th>Предметы</th>
                <th class="w-36">Нұсқа / Раздел</th>
                <th class="w-20 text-center">Вопросов</th>
                <th class="w-20 text-center">Попыток</th>
                <th class="w-32">Срок</th>
                <th class="w-20"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accesses as $access)
                @php
                    $electiveSubjects = $access->accessSubjects
                        ->filter(fn ($s) => ! $s->subject?->is_mandatory)
                        ->values();
                @endphp
                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.test-accesses.edit', $access) }}'">

                    <td class="text-text-muted text-xs font-mono hidden sm:table-cell">{{ $access->id }}</td>

                    {{-- Target --}}
                    <td>
                        @if ($access->user)
                            <div class="flex items-center gap-2.5">
                                <x-avatar :name="$access->user->name" size="sm" />
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-text truncate">{{ $access->user->name }}</p>
                                    <p class="text-xs text-text-muted font-mono">{{ $access->user->login }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                     style="background: {{ \App\Helpers\AvatarHelper::color($access->group->title) }}">
                                    <x-icon name="user-group" class="w-4 h-4 text-white" />
                                </div>
                                <p class="text-sm font-semibold text-text">{{ $access->group->title }}</p>
                            </div>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="text-center" onclick="event.stopPropagation()">
                        <form method="POST" action="{{ route('admin.test-accesses.toggle-active', $access) }}">
                            @csrf
                            @method('PATCH')
                            <label class="inline-flex items-center cursor-pointer" title="{{ $access->is_active ? 'Деактивировать доступ' : 'Активировать доступ' }}">
                                <div class="relative shrink-0">
                                    <input type="checkbox" class="sr-only peer" @checked($access->is_active)
                                           onchange="this.form.requestSubmit()">
                                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-success rounded-full transition-all duration-300 shadow-inner border border-gray-200 peer-focus:ring-2 peer-focus:ring-success/30"></div>
                                    <div class="absolute top-[2px] left-[2px] bg-white rounded-full h-5 w-5 shadow-md transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] peer-checked:translate-x-full"></div>
                                </div>
                            </label>
                        </form>
                    </td>

                    {{-- Type --}}
                    <td>
                        <span class="badge {{ $access->type->badgeClass() }}">{{ $access->type->label() }}</span>
                    </td>

                    {{-- Subjects --}}
                    <td class="text-sm text-text">
                        @if ($access->type === \App\Enums\TestAccessType::Ent)
                            <div class="space-y-0.5">
                                <p class="text-xs text-text-muted">3 обязательных</p>
                                @if ($access->student_chooses_subject)
                                    <p class="text-xs italic text-text-muted">+ студент выбирает</p>
                                @elseif ($electiveSubjects->isNotEmpty())
                                    @foreach ($electiveSubjects as $as)
                                        <p class="text-xs">+ {{ $as->subject?->title ?? '—' }}</p>
                                    @endforeach
                                @else
                                    <p class="text-xs text-text-muted">+ не выбраны</p>
                                @endif
                            </div>
                        @else
                            @php $single = $access->accessSubjects->first(); @endphp
                            <span>{{ $single?->subject?->title ?? '—' }}</span>
                        @endif
                    </td>

                    {{-- Нұсқа / Part --}}
                    <td class="text-sm">
                        @if ($access->type === \App\Enums\TestAccessType::Ent)
                            @if ($access->student_chooses_nusqa)
                                <span class="text-text-muted italic text-xs">студент выбирает</span>
                            @elseif ($access->nusqa_number)
                                <span class="badge badge-primary font-mono">Нұсқа {{ $access->nusqa_number }}</span>
                            @else
                                <span class="text-text-muted text-xs">случайно</span>
                            @endif
                        @else
                            @php $single = $access->accessSubjects->first(); @endphp
                            @if ($single?->part_type)
                                <span class="badge {{ $single->part_type->badgeClass() }}">{{ $single->part_type->label() }}</span>
                                @if ($single->student_chooses_part)
                                    <span class="text-xs text-text-muted ml-1">студент</span>
                                @elseif ($single->part?->title)
                                    <span class="text-xs text-text ml-1">{{ $single->part->title }}</span>
                                @endif
                            @else
                                <span class="text-text-muted text-xs">случайно</span>
                            @endif
                        @endif
                    </td>

                    {{-- Question count --}}
                    <td class="text-center">
                        @if ($access->question_count > 0)
                            <span class="font-mono font-bold text-sm text-text">{{ $access->question_count }}</span>
                        @else
                            <span class="text-text-muted text-xs">все</span>
                        @endif
                    </td>

                    {{-- Attempts --}}
                    <td class="text-center">
                        @if ($access->attempts_limit > 0)
                            <span class="font-mono font-bold text-sm text-text">{{ $access->attempts_limit }}</span>
                        @else
                            <span class="text-text-muted text-xs">∞</span>
                        @endif
                    </td>

                    {{-- Expires --}}
                    <td class="text-sm">
                        @if ($access->expires_at)
                            @if ($access->isExpired())
                                <span class="text-danger font-semibold">{{ $access->expires_at->format('d.m.Y H:i') }}</span>
                            @else
                                <span class="text-text">{{ $access->expires_at->format('d.m.Y H:i') }}</span>
                            @endif
                        @else
                            <span class="text-success text-xs font-semibold">Бессрочно</span>
                        @endif
                    </td>

                    <td onclick="event.stopPropagation()">
                        <div class="flex items-center gap-1">
                            <x-btn.edit :route="route('admin.test-accesses.edit', $access)" />
                            <x-btn.delete :route="route('admin.test-accesses.destroy', $access)" />
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-text-muted py-12">
                        @if (request()->hasAny(['search', 'type', 'target']))
                            По фильтрам ничего не найдено.
                        @else
                            Доступы не выданы.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($accesses->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $accesses->links() }}
        </div>
    @endif
</div>

@endsection
