<template x-teleport="body">
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 bg-black/85"
        @click.self="close()"
        @keydown.escape.window="open && close()"
        @keydown.equal.window="open && zoomIn()"
        @keydown.add.window="open && zoomIn()"
        @keydown.minus.window="open && zoomOut()"
        @wheel.prevent="open && onWheel($event)"
    >
        <div class="absolute top-4 inset-x-4 z-10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded bg-white/15 px-3 py-1.5 text-sm text-white hover:bg-white/25"
                    @click="zoomOut()"
                    title="Perkecil"
                >
                    − Perkecil
                </button>
                <button
                    type="button"
                    class="rounded bg-white/15 px-3 py-1.5 text-sm text-white hover:bg-white/25"
                    @click="zoomIn()"
                    title="Perbesar"
                >
                    + Perbesar
                </button>
                <button
                    type="button"
                    class="rounded bg-white/15 px-3 py-1.5 text-sm text-white hover:bg-white/25"
                    @click="resetTransform()"
                    title="Reset zoom"
                >
                    Reset
                </button>
                <span class="text-sm text-white/80 tabular-nums" x-text="Math.round(scale * 100) + '%'"></span>
            </div>
            <button type="button" class="rounded bg-white/15 px-3 py-1.5 text-sm text-white hover:bg-white/25" @click="close()">
                Tutup
            </button>
        </div>

        <div class="h-full w-full overflow-hidden flex items-center justify-center px-4 pt-16 pb-12" @click.self="close()">
            <img
                :src="src"
                :alt="title"
                :style="imgStyle"
                class="max-h-[80vh] max-w-[90vw] object-contain select-none"
                draggable="false"
                @dblclick.prevent="toggleZoom()"
                @pointerdown="onPointerDown($event)"
                @pointermove="onPointerMove($event)"
                @pointerup="onPointerUp($event)"
                @pointercancel="onPointerUp($event)"
            >
        </div>

        <p class="absolute bottom-4 left-0 right-0 text-center text-sm text-white" x-text="title"></p>
    </div>
</template>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('imageLightbox', () => ({
                open: false,
                src: '',
                title: '',
                scale: 1,
                minScale: 0.5,
                maxScale: 5,
                x: 0,
                y: 0,
                dragging: false,
                startX: 0,
                startY: 0,
                pointers: {},
                pinchStartDist: 0,
                pinchStartScale: 1,

                get imgStyle() {
                    const cursor = this.scale > 1 ? (this.dragging ? 'grabbing' : 'grab') : 'zoom-in';
                    const transition = this.dragging ? 'none' : 'transform 0.15s ease';

                    return `transform: translate(${this.x}px, ${this.y}px) scale(${this.scale}); transform-origin: center center; cursor: ${cursor}; transition: ${transition}; touch-action: none;`;
                },

                show(src, title = '') {
                    this.src = src;
                    this.title = title || 'Foto';
                    this.resetTransform();
                    this.open = true;
                },

                close() {
                    this.open = false;
                    this.resetTransform();
                },

                resetTransform() {
                    this.scale = 1;
                    this.x = 0;
                    this.y = 0;
                    this.dragging = false;
                    this.pointers = {};
                    this.pinchStartDist = 0;
                },

                clamp(value) {
                    return Math.min(this.maxScale, Math.max(this.minScale, Math.round(value * 100) / 100));
                },

                zoomIn() {
                    this.scale = this.clamp(this.scale + 0.25);
                },

                zoomOut() {
                    this.scale = this.clamp(this.scale - 0.25);
                    if (this.scale <= 1) {
                        this.x = 0;
                        this.y = 0;
                    }
                },

                toggleZoom() {
                    if (this.scale > 1) {
                        this.resetTransform();
                        return;
                    }
                    this.scale = 2;
                },

                onWheel(event) {
                    if (event.deltaY < 0) {
                        this.zoomIn();
                        return;
                    }
                    this.zoomOut();
                },

                onPointerDown(event) {
                    event.currentTarget.setPointerCapture(event.pointerId);
                    this.pointers[event.pointerId] = { x: event.clientX, y: event.clientY };
                    const points = Object.values(this.pointers);
                    if (points.length === 2) {
                        this.dragging = false;
                        this.pinchStartDist = this.distance(points[0], points[1]);
                        this.pinchStartScale = this.scale;
                        return;
                    }
                    this.dragging = true;
                    this.startX = event.clientX - this.x;
                    this.startY = event.clientY - this.y;
                },

                onPointerMove(event) {
                    if (this.pointers[event.pointerId]) {
                        this.pointers[event.pointerId] = { x: event.clientX, y: event.clientY };
                    }
                    const points = Object.values(this.pointers);
                    if (points.length === 2 && this.pinchStartDist) {
                        this.scale = this.clamp(this.pinchStartScale * (this.distance(points[0], points[1]) / this.pinchStartDist));
                        return;
                    }
                    if (! this.dragging) {
                        return;
                    }
                    this.x = event.clientX - this.startX;
                    this.y = event.clientY - this.startY;
                },

                onPointerUp(event) {
                    delete this.pointers[event.pointerId];
                    this.dragging = false;
                    this.pinchStartDist = 0;
                },

                distance(a, b) {
                    return Math.hypot(a.x - b.x, a.y - b.y);
                },
            }));
        });
    </script>
@endonce
