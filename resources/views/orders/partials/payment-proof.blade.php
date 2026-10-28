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
            <div class="mb-3" x-data="imageLightbox()">
                <button
                    type="button"
                    class="block cursor-zoom-in"
                    @click="show(@js($previewUrl), 'Bukti Pembayaran')"
                >
                    <img
                        src="{{ $previewUrl }}"
                        alt="Bukti pembayaran"
                        class="rounded-md w-full max-w-xs object-cover max-h-48 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
                    >
                </button>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik gambar untuk memperbesar, lalu zoom in/out</p>
                @include('orders.partials.image-lightbox')
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
