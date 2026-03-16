@props(['label', 'name', 'value' => null, 'checked' => false])

@php
    $id = $name . '_' . ($value ?? uniqid());
@endphp

<label for="{{ $id }}"
    {{ $attributes->merge(['class' => 'flex items-center text-sm text-gray-700 dark:text-gray-300 cursor-pointer']) }}>
    <input type="checkbox" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2">
    <span>{{ $label }}</span>
</label>

@error($name)
    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
@enderror
