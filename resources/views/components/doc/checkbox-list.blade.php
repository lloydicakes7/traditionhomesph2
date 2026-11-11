{{--
    The two-column tick list the certification and the business permit print:
    a small box and its label on one baseline, one row per option, filled down
    the first column and then the second. The row pitch belongs to the class,
    so a list with nine options and a list with six keep the same rhythm.

    An option may carry a blank after its label ("Other: ____"); that blank is
    part of the option, so its cell always prints it, ticked or not.
--}}
@props([
    'options' => [],
    'columns' => 2,
])

@php
    $items = array_values((array) $options);
    $count = max(1, (int) $columns);
    $perColumn = max(1, (int) ceil(count($items) / $count));
    $width = round(100 / $count, 4);
@endphp

<table class="doc-check-list">
    <colgroup>
        @for ($column = 0; $column < $count; $column++)
            <col style="width: {{ $width }}%">
        @endfor
    </colgroup>
    @for ($row = 0; $row < $perColumn; $row++)
        <tr>
            @for ($column = 0; $column < $count; $column++)
                @php($option = $items[$column * $perColumn + $row] ?? null)
                <td class="doc-check-list__cell">
                    @if ($option !== null)
                        <x-doc.checkbox :checked="$option['checked'] ?? false" />
                        <span class="doc-check-list__label">{{ $option['label'] }}</span>
                        @if (($option['blank'] ?? null) !== null)
                            <x-doc.line :w="$option['blank']" :value="$option['value'] ?? ''" />
                        @endif
                    @endif
                </td>
            @endfor
        </tr>
    @endfor
</table>
