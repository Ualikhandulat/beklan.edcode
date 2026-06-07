@props(['show' => 'calcOpen'])

@php
    $base  = 'h-11 rounded-xl text-sm font-extrabold transition-colors cursor-pointer flex items-center justify-center';
    $num   = $base . ' bg-gray-100 text-text hover:bg-gray-200';
    $fn    = $base . ' bg-primary/8 text-primary text-xs hover:bg-primary/15';
    $op    = $base . ' bg-gray-200 text-text hover:bg-gray-300';
    $clear = $base . ' bg-danger/10 text-danger hover:bg-danger/20';
    $eq    = $base . ' text-white';
@endphp

<x-modal title="Калькулятор" :show="$show" size="sm">
    <div
        x-data="{
            display: '0',
            acc: null,
            op: null,
            overwrite: true,
            deg: true,

            digit(d) {
                this.display = this.overwrite ? d : (this.display === '0' ? d : this.display + d);
                this.overwrite = false;
            },
            decimal() {
                if (this.overwrite) { this.display = '0.'; this.overwrite = false; return; }
                if (!this.display.includes('.')) { this.display += '.'; }
            },
            clear() {
                this.display = '0';
                this.acc = null;
                this.op = null;
                this.overwrite = true;
            },
            chooseOp(next) {
                if (this.op !== null && !this.overwrite) { this.compute(); }
                this.acc = parseFloat(this.display);
                this.op = next;
                this.overwrite = true;
            },
            compute() {
                if (this.op === null || this.acc === null) { return; }
                const cur = parseFloat(this.display);
                const result = {
                    '+': this.acc + cur,
                    '-': this.acc - cur,
                    '*': this.acc * cur,
                    '/': cur === 0 ? NaN : this.acc / cur,
                }[this.op];
                this.display = this.format(result);
                this.acc = null;
                this.op = null;
                this.overwrite = true;
            },
            percent() {
                this.display = this.format(parseFloat(this.display) / 100);
                this.overwrite = true;
            },
            unary(name) {
                const v = parseFloat(this.display);
                const rad = this.deg ? v * Math.PI / 180 : v;
                const result = {
                    sin:  Math.sin(rad),
                    cos:  Math.cos(rad),
                    tan:  Math.tan(rad),
                    log:  Math.log10(v),
                    ln:   Math.log(v),
                    sqrt: Math.sqrt(v),
                    sqr:  v * v,
                }[name];
                this.display = this.format(result);
                this.overwrite = true;
            },
            pi() {
                this.display = this.format(Math.PI);
                this.overwrite = false;
            },
            format(n) {
                if (!isFinite(n)) { return 'Ошибка'; }
                return parseFloat(n.toPrecision(12)).toString();
            },
        }"
        class="p-5"
    >
        {{-- Display --}}
        <div class="rounded-2xl bg-gray-50 border border-border px-4 py-4 mb-3 text-right overflow-hidden">
            <p class="text-2xl font-extrabold text-text font-mono tabular-nums truncate" x-text="display"></p>
        </div>

        {{-- Degrees / radians --}}
        <div class="flex items-center justify-end gap-1.5 mb-3">
            <button type="button" @click="deg = true"
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer"
                    :class="deg ? 'bg-primary text-white' : 'bg-gray-100 text-text-muted hover:bg-gray-200'">
                град
            </button>
            <button type="button" @click="deg = false"
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer"
                    :class="!deg ? 'bg-primary text-white' : 'bg-gray-100 text-text-muted hover:bg-gray-200'">
                рад
            </button>
        </div>

        {{-- Buttons --}}
        <div class="grid grid-cols-4 gap-2">
            <button type="button" @click="unary('sin')" class="{{ $fn }}">sin</button>
            <button type="button" @click="unary('cos')" class="{{ $fn }}">cos</button>
            <button type="button" @click="unary('tan')" class="{{ $fn }}">tan</button>
            <button type="button" @click="clear()" class="{{ $clear }}">C</button>

            <button type="button" @click="unary('log')" class="{{ $fn }}">log</button>
            <button type="button" @click="unary('ln')" class="{{ $fn }}">ln</button>
            <button type="button" @click="unary('sqrt')" class="{{ $fn }}">√</button>
            <button type="button" @click="unary('sqr')" class="{{ $fn }}">x²</button>

            <button type="button" @click="digit('7')" class="{{ $num }}">7</button>
            <button type="button" @click="digit('8')" class="{{ $num }}">8</button>
            <button type="button" @click="digit('9')" class="{{ $num }}">9</button>
            <button type="button" @click="chooseOp('/')" class="{{ $op }}">÷</button>

            <button type="button" @click="digit('4')" class="{{ $num }}">4</button>
            <button type="button" @click="digit('5')" class="{{ $num }}">5</button>
            <button type="button" @click="digit('6')" class="{{ $num }}">6</button>
            <button type="button" @click="chooseOp('*')" class="{{ $op }}">×</button>

            <button type="button" @click="digit('1')" class="{{ $num }}">1</button>
            <button type="button" @click="digit('2')" class="{{ $num }}">2</button>
            <button type="button" @click="digit('3')" class="{{ $num }}">3</button>
            <button type="button" @click="chooseOp('-')" class="{{ $op }}">−</button>

            <button type="button" @click="pi()" class="{{ $num }}">π</button>
            <button type="button" @click="digit('0')" class="{{ $num }}">0</button>
            <button type="button" @click="decimal()" class="{{ $num }}">.</button>
            <button type="button" @click="chooseOp('+')" class="{{ $op }}">+</button>

            <button type="button" @click="percent()" class="{{ $num }}">%</button>
            <button type="button" @click="compute()" class="{{ $eq }} col-span-3" style="background: var(--color-primary)">=</button>
        </div>
    </div>
</x-modal>
