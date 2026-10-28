@php
    $tableClass = 'min-w-full text-sm';
    $thClass = 'py-2 px-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700';
    $tdClass = 'py-2 px-3 text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700/50';
    $extraKey = $extraKey ?? null;
    $extraLabel = $extraLabel ?? __('Nomor order');
    $scroll = $scroll ?? false;
    $colspan = $extraKey ? 4 : 3;
@endphp

<div class="overflow-x-auto {{ $scroll ? 'max-h-[32rem] overflow-y-auto' : '' }}">
    <table class="{{ $tableClass }}">
        <thead class="{{ $scroll ? 'sticky top-0 bg-white dark:bg-gray-800' : '' }}">
            <tr>
                <th class="{{ $thClass }}">#</th>
                <th class="{{ $thClass }}">{{ __('User') }}</th>
                @if($extraKey)
                    <th class="{{ $thClass }}">{{ $extraLabel }}</th>
                @endif
                <th class="{{ $thClass }} text-right">{{ $valueLabel }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                    <td class="{{ $tdClass }}">
                        <div class="font-medium">{{ $row->name }}</div>
                        <div class="text-xs text-gray-500">{{ $row->email }}</div>
                    </td>
                    @if($extraKey)
                        <td class="{{ $tdClass }} text-xs text-gray-600 dark:text-gray-300">{{ $row->{$extraKey} }}</td>
                    @endif
                    <td class="{{ $tdClass }} text-right font-semibold">
                        @if(!empty($formatKm))
                            {{ number_format((float) $row->{$valueKey}, 0, ',', '.') }}
                        @else
                            {{ number_format($row->{$valueKey}) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $colspan }}" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
