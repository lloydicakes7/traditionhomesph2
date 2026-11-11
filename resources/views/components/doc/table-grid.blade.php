@props([
    'columns' => [],
    'rows'    => [],
    'minRows' => 0,
])

@php
    $columns = (array) $columns;
    $rowCount = max((int) $minRows, count($rows));
    $blank = array_fill(0, count($columns), '');
@endphp

<table class="doc-grid">
    <colgroup>
        @foreach ($columns as $width)
            <col style="width: {{ (float) $width }}%">
        @endforeach
    </colgroup>
    <thead>
        <tr>
            @foreach (array_keys($columns) as $label)
                <th>{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @for ($index = 0; $index < $rowCount; $index++)
            @php($row = $rows[$index] ?? $blank)
            <tr>
                @foreach (array_keys($columns) as $key)
                    <td>{{ \App\Utility\DocumentText::clean($row[$key] ?? '') }}</td>
                @endforeach
            </tr>
        @endfor
    </tbody>
</table>
