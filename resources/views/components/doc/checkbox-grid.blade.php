@props([
    'options'  => [],
    'selected' => '',
])

@php
    $picked = \App\Utility\DocumentText::clean($selected);
    $count = max(1, count($options));
    $width = round(100 / $count, 4);
@endphp

<table class="doc-box-grid">
    <colgroup>
        @for ($i = 0; $i < $count; $i++)
            <col style="width: {{ $width }}%">
        @endfor
    </colgroup>
    <tr>
        @foreach ($options as $option)
            @php($label = \App\Utility\DocumentText::clean($option))
            <td>
                <x-doc.checkbox :checked="$picked !== '' && strcasecmp($label, $picked) === 0" size="large" />
                <div class="doc-box-grid__label">{{ $label }}</div>
            </td>
        @endforeach
    </tr>
</table>
