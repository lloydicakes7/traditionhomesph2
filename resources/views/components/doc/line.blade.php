@props([
    'w'     => 'name',
    'value' => '',
    'align' => 'left',
])

@php
    $widths = (array) config('documents.widths', []);
    $mm = isset($widths[$w]) ? (float) $widths[$w] : (is_numeric($w) ? (float) $w : 55.0);

    $text = \App\Utility\DocumentText::clean($value);
    $text = \App\Utility\DocumentText::fit(
        $text,
        max(1, (int) floor($mm * (float) config('documents.chars_per_mm', 0.57)))
    );
@endphp

<span class="doc-line doc-line--{{ $align }}" style="width: {{ $mm }}mm;">{!! $text !== '' ? e($text) : '&#160;' !!}</span>
