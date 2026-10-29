@php
    $src = $src ?? '';
    $title = $title ?? 'Foto';
    $alt = $alt ?? $title;
    $class = $class ?? 'rounded-md max-h-64 w-full object-contain bg-gray-100 dark:bg-gray-900';
@endphp

<button
    type="button"
    class="block w-full cursor-zoom-in"
    @click="show(@js($src), @js($title))"
>
    <img src="{{ $src }}" alt="{{ $alt }}" class="{{ $class }}">
</button>
<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik gambar untuk memperbesar, lalu zoom in/out</p>
