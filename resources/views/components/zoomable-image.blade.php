@props(['src', 'alt' => ''])

<div
    x-data="{
        scale: 1,
        minScale: 1,
        maxScale: 4,
        tx: 0,
        ty: 0,
        startDist: 0,
        startScale: 1,
        startX: 0,
        startY: 0,
        dragging: false,

        clampScale(s) {
            return Math.min(this.maxScale, Math.max(this.minScale, s));
        },

        zoomIn() {
            this.scale = this.clampScale(this.scale + 0.5);
            if (this.scale === 1) { this.tx = 0; this.ty = 0; }
        },
        zoomOut() {
            this.scale = this.clampScale(this.scale - 0.5);
            if (this.scale === 1) { this.tx = 0; this.ty = 0; }
        },
        reset() {
            this.scale = 1; this.tx = 0; this.ty = 0;
        },

        touchDistance(touches) {
            const [a, b] = touches;
            return Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY);
        },

        onTouchStart(e) {
            if (e.touches.length === 2) {
                this.startDist = this.touchDistance(e.touches);
                this.startScale = this.scale;
            } else if (e.touches.length === 1 && this.scale > 1) {
                this.dragging = true;
                this.startX = e.touches[0].clientX - this.tx;
                this.startY = e.touches[0].clientY - this.ty;
            }
        },
        onTouchMove(e) {
            if (e.touches.length === 2 && this.startDist > 0) {
                e.preventDefault();
                const ratio = this.touchDistance(e.touches) / this.startDist;
                this.scale = this.clampScale(this.startScale * ratio);
            } else if (e.touches.length === 1 && this.dragging) {
                e.preventDefault();
                this.tx = e.touches[0].clientX - this.startX;
                this.ty = e.touches[0].clientY - this.startY;
            }
        },
        onTouchEnd(e) {
            if (e.touches.length === 0) {
                this.dragging = false;
                this.startDist = 0;
                if (this.scale <= 1) { this.scale = 1; this.tx = 0; this.ty = 0; }
            }
        },
    }"
    class="relative bg-gray-50 rounded-xl border border-border overflow-hidden touch-none select-none"
    style="height: 70vh"
>
    <div class="absolute inset-0 flex items-center justify-center overflow-hidden"
         @touchstart="onTouchStart($event)"
         @touchmove="onTouchMove($event)"
         @touchend="onTouchEnd($event)"
         @touchcancel="onTouchEnd($event)">
        <img src="{{ $src }}" alt="{{ $alt }}" draggable="false"
             class="max-w-full max-h-full select-none"
             :style="`transform: translate(${tx}px, ${ty}px) scale(${scale})`">
    </div>

    {{-- Zoom controls --}}
    <div class="absolute bottom-3 right-3 flex items-center gap-1 bg-white/95 rounded-xl border border-border shadow-sm p-1">
        <button type="button" @click="zoomOut()"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-text font-extrabold text-lg hover:bg-gray-100 cursor-pointer">−</button>
        <span class="text-xs font-bold text-text-muted w-11 text-center" x-text="Math.round(scale * 100) + '%'"></span>
        <button type="button" @click="zoomIn()"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-text font-extrabold text-lg hover:bg-gray-100 cursor-pointer">+</button>
        <button type="button" @click="reset()"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-text-muted hover:bg-gray-100 cursor-pointer">
            <x-icon name="refresh" class="w-4 h-4" />
        </button>
    </div>

    {{-- Hint --}}
    <p class="absolute top-3 left-3 right-3 text-[11px] font-semibold text-text-muted bg-white/90 rounded-lg px-2.5 py-1 inline-block w-fit lg:hidden">
        Сведите/разведите пальцы для масштабирования
    </p>
</div>
