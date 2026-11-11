@props([
    'options'  => [],
    'selected' => '',
    'separator' => '/',
])

@php
    $picked = \App\Utility\DocumentText::clean($selected);

    $labels = [];
    $chosen = null;

    foreach ($options as $key => $label) {
        $label = \App\Utility\DocumentText::clean($label);
        $labels[] = $label;

        if ($picked !== '' && (strcasecmp((string) $key, $picked) === 0 || strcasecmp($label, $picked) === 0)) {
            $chosen = $label;
        }
    }
@endphp

<span class="doc-choice">
    @if ($chosen !== null)
        <strong>{{ $chosen }}</strong>
    @else
        {{ implode($separator, $labels) }}
    @endif
</span>
