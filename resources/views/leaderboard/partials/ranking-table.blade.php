@php
    $tableClass = 'min-w-full text-sm';
    $thClass = 'py-2 px-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700';
    $tdClass = 'py-2 px-3 text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700/50';
    $extraKey = $extraKey ?? null;
    $extraLabel = $extraLabel ?? __('Nomor order');
    $statusCountColumns = $statusCountColumns ?? [];
    $scroll = $scroll ?? false;
    $showColor = $showColor ?? false;
    $hideValue = $hideValue ?? false;
    $colspan = 2 + ($extraKey ? 1 : 0) + count($statusCountColumns) + ($hideValue ? 0 : 1);
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
                @foreach($statusCountColumns as $statusColumn)
                    <th class="{{ $thClass }} text-right">{{ $statusColumn['label'] }}</th>
                @endforeach
                @unless($hideValue)
                    <th class="{{ $thClass }} text-right">{{ $valueLabel }}</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                    <td class="{{ $tdClass }}">
                        <div class="flex items-center gap-2">
                            @if($showColor && !empty($row->color))
                                <span class="inline-block w-3 h-3 rounded-full shrink-0 ring-2 ring-white dark:ring-gray-800 shadow" style="background-color: {{ $row->color }}"></span>
                            @endif
                            <div>
                                <div class="font-medium">{{ $row->name }}</div>
                                <div class="text-xs text-gray-500">{{ $row->email }}</div>
                            </div>
                        </div>
                    </td>
                    @if($extraKey)
                        <td class="{{ $tdClass }} text-xs text-gray-600 dark:text-gray-300">{{ $row->{$extraKey} }}</td>
                    @endif
                    @foreach($statusCountColumns as $statusColumn)
                        <td class="{{ $tdClass }} text-right font-semibold">{{ number_format((int) ($row->{$statusColumn['key']} ?? 0)) }}</td>
                    @endforeach
                    @unless($hideValue)
                        <td class="{{ $tdClass }} text-right font-semibold">
                            @if(!empty($formatKm))
                                {{ number_format((float) $row->{$valueKey}, 0, ',', '.') }}
                            @else
                                {{ number_format($row->{$valueKey}) }}
                            @endif
                        </td>
                    @endunless
                </tr>
            @empty
                <tr><td colspan="{{ $colspan }}" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
