@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-outline btn-sm">
        <x-icon name="pencil" class="w-4 h-4" />
        Редактировать
    </a>
@endsection

@section('content')

<div class="flex gap-4"
     style="height: calc(100vh - 10rem)"
     x-data="{
         showSearch:   false,
         showConfirm:  false,
         studentSearch: '',
         addSearch:    '',
         selectedUser: null,
         available: {{ Js::from($available->map(fn($u) => ['id' => $u->id, 'name' => $u->name])) }},

         avatarColor(name) {
             const p = ['#E53E3E','#DD6B20','#D69E2E','#38A169','#3182CE','#805AD5','#D53F8C','#319795','#2F855A','#2B6CB0','#6B46C1','#C05621'];
             let s = 0; for (const c of String(name)) s += c.codePointAt(0);
             return p[s % p.length];
         },
         avatarInitials(name) {
             const w = String(name).trim().split(/\s+/);
             return w.length >= 2
                 ? ([...w[0]][0] + [...w[1]][0]).toUpperCase()
                 : [...String(name).trim()].slice(0, 2).join('').toUpperCase();
         },

         openSearch() {
             this.addSearch    = '';
             this.selectedUser = null;
             this.showSearch   = true;
         },
         pickUser(u) {
             this.selectedUser = u;
             this.showSearch   = false;
             this.showConfirm  = true;
         },
         backToSearch() {
             this.showConfirm = false;
             this.showSearch  = true;
         },
         confirmAdd() {
             this.$refs.addForm.submit();
         },

         get filteredAvailable() {
             const q = this.addSearch.toLowerCase().trim();
             return q ? this.available.filter(u => u.name.toLowerCase().includes(q)) : this.available;
         }
     }">

    {{-- ── Left: All Groups (1/3) ─────────────────────────────────────── --}}
    <div class="w-1/3 bg-white rounded-2xl border border-border flex flex-col overflow-hidden"
         style="box-shadow: var(--shadow-card)">

        <div class="px-4 py-3.5 border-b border-border shrink-0 flex items-center justify-between">
            <span class="text-xs font-extrabold text-text-muted uppercase tracking-widest">Все группы</span>
            <span class="text-xs font-bold text-text-muted">{{ $groups->count() }}</span>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-border">
            @foreach ($groups as $g)
                <a href="{{ route('admin.groups.show', $g) }}"
                   class="flex items-center gap-3 px-4 py-3 transition-colors {{ $g->id === $group->id ? 'bg-primary-light' : 'hover:bg-gray-50' }}">

                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                         style="background-color: {{ \App\Helpers\AvatarHelper::color($g->title) }}">
                        <x-icon name="user-group" class="w-4 h-4 text-white" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate {{ $g->id === $group->id ? 'text-primary' : 'text-text' }}">
                            {{ $g->title }}
                        </p>
                        <p class="text-xs text-text-muted truncate">{{ $g->description }}</p>
                    </div>

                    <span class="text-base font-bold border border-border rounded-full w-8 h-8 flex items-center justify-center shrink-0 {{ $g->id === $group->id ? 'border-primary/30 text-primary' : 'text-text-muted' }}">
                        {{ $g->users_count }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── Right: Students (2/3) ──────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 bg-white rounded-2xl border border-border flex flex-col overflow-hidden"
         style="box-shadow: var(--shadow-card)">

        {{-- Header --}}
        <div class="px-5 py-3.5 border-b border-border flex items-center gap-3 shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                 style="background-color: {{ \App\Helpers\AvatarHelper::color($group->title) }}">
                <x-icon name="user-group" class="w-5 h-5 text-white" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-extrabold text-text truncate">{{ $group->title }}</p>
                @if ($group->description)
                    <p class="text-xs text-text-muted truncate">{{ $group->description }}</p>
                @endif
            </div>
            <span class="badge badge-info shrink-0">{{ $students->count() }} студ.</span>
            <button @click="openSearch()"
                    class="btn btn-success btn-sm shrink-0">
                <x-icon name="plus" class="w-4 h-4" />
                Добавить
            </button>
        </div>

        {{-- Student search --}}
        <div class="px-5 py-2.5 border-b border-border shrink-0">
            <div class="relative">
                <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
                <input type="search"
                       x-model="studentSearch"
                       placeholder="Поиск по студентам..."
                       class="pl-9 py-2 text-sm">
            </div>
        </div>

        {{-- Students list --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-border">
            @forelse ($students as $student)
                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors"
                     x-show="!studentSearch.trim() || {{ Js::from(mb_strtolower($student->name)) }}.includes(studentSearch.toLowerCase().trim())">
                    <x-avatar :name="$student->name" size="md" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-text">{{ $student->name }}</p>
                        <p class="text-xs text-text-muted font-mono">{{ $student->login }}</p>
                    </div>
                    <form method="POST"
                          action="{{ route('admin.groups.students.remove', [$group, $student]) }}"
                          onsubmit="return confirm('Удалить {{ addslashes($student->name) }} из группы?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light">
                            <x-icon name="x" class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center py-20">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <x-icon name="users" class="w-7 h-7 text-text-muted" />
                    </div>
                    <p class="text-sm font-bold text-text">В группе нет студентов</p>
                    <p class="text-xs text-text-muted mt-1">Нажмите «Добавить» чтобы добавить студентов</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Hidden form — inside x-data scope so $refs works --}}
    <form x-ref="addForm"
          method="POST"
          action="{{ route('admin.groups.students.add', $group) }}"
          class="hidden">
        @csrf
        <input type="hidden" name="user_id" :value="selectedUser?.id">
    </form>

    {{-- ── Modal 1: Search student ─────────────────────────────────────── --}}
    <x-modal title="Добавить студента" show="showSearch">

        <div class="px-4 py-3 border-b border-border">
            <div class="relative">
                <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
                <input type="search"
                       x-model="addSearch"
                       x-init="$watch('showSearch', v => { if (v) $nextTick(() => $el.focus()) })"
                       placeholder="Поиск по имени..."
                       class="pl-9 py-2 text-sm">
            </div>
        </div>

        <div class="p-2">
            <template x-if="filteredAvailable.length === 0">
                <p class="text-sm text-text-muted text-center py-10">Нет доступных студентов</p>
            </template>
            <template x-for="u in filteredAvailable" :key="u.id">
                <button type="button"
                        @click="pickUser(u)"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-left">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 font-extrabold text-white text-[11px]"
                         :style="{ backgroundColor: avatarColor(u.name) }">
                        <span x-text="avatarInitials(u.name)"></span>
                    </div>
                    <span class="flex-1 text-sm font-semibold text-text" x-text="u.name"></span>
                    <x-icon name="plus" class="w-4 h-4 text-text-muted shrink-0" />
                </button>
            </template>
        </div>

    </x-modal>

    {{-- ── Modal 2: Confirm add ────────────────────────────────────────── --}}
    <x-modal title="Подтвердить добавление" show="showConfirm" size="sm">

        <div class="px-5 py-6 flex flex-col items-center text-center gap-4">
            <template x-if="selectedUser">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center font-extrabold text-white text-xl"
                         :style="{ backgroundColor: avatarColor(selectedUser.name) }">
                        <span x-text="avatarInitials(selectedUser.name)"></span>
                    </div>
                    <div>
                        <p class="font-extrabold text-text text-lg" x-text="selectedUser.name"></p>
                        <p class="text-sm text-text-muted mt-0.5">
                            будет добавлен(а) в группу
                            <span class="font-bold text-text">{{ $group->title }}</span>
                        </p>
                    </div>
                </div>
            </template>
        </div>

        <x-slot:footer>
            <button type="button"
                    @click="backToSearch()"
                    class="btn btn-outline btn-sm">
                Назад
            </button>
            <button type="button"
                    @click="confirmAdd()"
                    class="btn btn-success btn-sm">
                <x-icon name="check" class="w-4 h-4" />
                Добавить
            </button>
        </x-slot:footer>

    </x-modal>

</div>
@endsection
