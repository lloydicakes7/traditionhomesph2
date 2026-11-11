@props([
    'rows'  => 2,
    'lines' => [],
    'chars' => 90,
])

@php
    $source = is_array($lines) ? implode(' ', array_map(function ($line) {
        return \App\Utility\DocumentText::clean($line);
    }, $lines)) : \App\Utility\DocumentText::clean($lines);

    $wrapped = \App\Utility\DocumentText::wrap(
        $source,
        max(10, (int) $chars),
        max(1, (int) $rows)
    );

    $needed = max((int) $rows, count($wrapped));

    if (count($wrapped) > (int) $rows) {
        \Illuminate\Support\Facades\Log::warning('Printed document needed more ruled lines than the paper carries.', [
            'paper_rows' => (int) $rows,
            'needed_rows' => count($wrapped),
        ]);
    }
@endphp

<table class="doc-ruled">
    @for ($index = 0; $index < $needed; $index++)
        <tr>
            <td>{!! isset($wrapped[$index]) ? e($wrapped[$index]) : '&#160;' !!}</td>
        </tr>
    @endfor
</table>
