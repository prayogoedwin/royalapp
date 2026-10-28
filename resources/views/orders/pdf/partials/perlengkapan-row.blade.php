@php
    $status = $checklist_map[$item] ?? '';
@endphp
<tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
    <td class="item">{{ $item }}</td>
    <td class="mark">
        @if($editable)
            <label class="mark-choice">
                <input type="radio" name="perlengkapan[{{ md5($item) }}]" value="ada" data-item="{{ $item }}" @checked($status === 'ada')>
                <span class="tick">✓</span>
            </label>
        @elseif($status === 'ada')
            ✓
        @endif
    </td>
    <td class="mark">
        @if($editable)
            <label class="mark-choice">
                <input type="radio" name="perlengkapan[{{ md5($item) }}]" value="tidak" data-item="{{ $item }}" @checked($status === 'tidak')>
                <span class="tick">✓</span>
            </label>
        @elseif($status === 'tidak')
            ✓
        @endif
    </td>
</tr>
