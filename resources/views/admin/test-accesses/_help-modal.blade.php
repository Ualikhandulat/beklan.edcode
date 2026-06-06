{{-- Help modal for the test-access create/edit form --}}
<div x-data="{ open: false }"
     @open-help-modal.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative z-10 w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border bg-primary/5">
            <div class="flex items-center gap-2.5">
                <x-icon name="information-circle" class="w-5 h-5 text-primary shrink-0" />
                <h2 class="font-extrabold text-text">Как работает страница доступов</h2>
            </div>
            <button @click="open = false" class="btn btn-ghost btn-sm text-text-muted hover:text-text p-1">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar text-sm text-text">

            <section>
                <h3 class="font-extrabold text-sm mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black shrink-0">1</span>
                    Кому выдаётся доступ
                </h3>
                <p class="text-text-muted leading-relaxed pl-8">
                    Выберите <strong>Студент</strong> — доступ получит конкретный студент.
                    Выберите <strong>Группа</strong> — доступ получат все студенты в группе.
                </p>
            </section>

            <section>
                <h3 class="font-extrabold text-sm mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black shrink-0">2</span>
                    Тип теста
                </h3>
                <div class="pl-8 space-y-2 text-text-muted leading-relaxed">
                    <p><strong class="text-text">ЕНТ</strong> — тест из 5 предметов: 3 обязательных + 2 выборных.
                        Вопросы берутся из нұсқа каждого предмета.</p>
                    <p><strong class="text-text">Предмет</strong> — тест по одному предмету.
                        Можно выбрать тип раздела: случайно, по теме или по нұсқа.</p>
                </div>
            </section>

            <section>
                <h3 class="font-extrabold text-sm mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black shrink-0">3</span>
                    ЕНТ — нұсқа
                </h3>
                <div class="pl-8 space-y-1.5 text-text-muted leading-relaxed">
                    <p><strong class="text-text">Случайные вопросы</strong> — вопросы выбираются случайно из всех нұсқа каждого предмета.</p>
                    <p><strong class="text-text">Студент выбирает нұсқа</strong> — студент сам укажет номер нұсқа перед началом теста.</p>
                    <p><strong class="text-text">Конкретная нұсқа</strong> — укажите номер нұсқа. Система проверит, есть ли нұсқа с таким номером в каждом из 5 предметов, и покажет количество вопросов.
                        Если в каком-то предмете нұсқа не найдена — сохранить не получится.</p>
                </div>
            </section>

            <section>
                <h3 class="font-extrabold text-sm mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black shrink-0">4</span>
                    Предмет — тип вопросов
                </h3>
                <div class="pl-8 space-y-1.5 text-text-muted leading-relaxed">
                    <p><strong class="text-text">Случайно</strong> — вопросы берутся случайно из всего предмета.</p>
                    <p><strong class="text-text">Тема / Нұсқа</strong> — вопросы берутся из определённого типа раздела.
                        Можно выбрать конкретный раздел, дать студенту выбрать самому или взять случайный.</p>
                </div>
            </section>

            <section>
                <h3 class="font-extrabold text-sm mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-black shrink-0">5</span>
                    Ограничения
                </h3>
                <div class="pl-8 space-y-1.5 text-text-muted leading-relaxed">
                    <p><strong class="text-text">Вопросов</strong> — только для типа «Предмет». Сколько вопросов будет в тесте. 0 = все. Для ЕНТ количество задано стандартом.</p>
                    <p><strong class="text-text">Попыток</strong> — сколько раз студент может пройти тест. По умолчанию 1. 0 = без ограничений.</p>
                    <p><strong class="text-text">Срок доступа</strong> — до какой даты доступ активен. Если не указан — бессрочно.</p>
                </div>
            </section>

        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border flex justify-end">
            <button @click="open = false" class="btn btn-primary btn-sm">Понятно</button>
        </div>
    </div>
</div>
