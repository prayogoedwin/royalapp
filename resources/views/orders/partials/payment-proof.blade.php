@php
    $showInput = $showInput ?? false;
    $showPreview = $showPreview ?? false;
    $order = $order ?? null;
    $previewUrl = $order?->foto_bukti_pembayaran_url;
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Bukti Pembayaran
        @if($showInput)
            <span class="font-normal text-gray-500 dark:text-gray-400">(opsional)</span>
        @endif
    </label>

    @if($showPreview)
        @if($previewUrl)
            <div class="mb-3" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button
                    type="button"
                    class="block cursor-zoom-in"
                    @click="open = true"
                >
                    <img
                        src="{{ $previewUrl }}"
                        alt="Bukti pembayaran"
                        class="rounded-md w-full max-w-xs object-cover max-h-48 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
                    >
                </button>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik gambar untuk memperbesar</p>

                <template x-teleport="body">
                    <div
                        x-show="open"
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                        @click.self="open = false"
                    >
                        <button type="button" class="absolute top-4 right-4 text-white text-sm" @click="open = false">Tutup</button>
                        <figure class="max-h-[90vh] max-w-5xl">
                            <img src="{{ $previewUrl }}" alt="Bukti pembayaran" class="max-h-[85vh] max-w-full object-contain">
                            <figcaption class="mt-2 text-center text-sm text-white">Bukti Pembayaran</figcaption>
                        </figure>
                    </div>
                </template>
            </div>
        @else
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Belum ada bukti pembayaran.</p>
        @endif
    @endif

    @if($showInput)
        <input
            type="file"
            name="payment_proof"
            accept="image/jpeg,image/png,image/jpg,image/webp"
            class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700"
        >
        @error('payment_proof')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @if($showPreview && $previewUrl)
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unggah file baru untuk mengganti bukti yang sudah ada.</p>
        @endif
    @endif
</div>
