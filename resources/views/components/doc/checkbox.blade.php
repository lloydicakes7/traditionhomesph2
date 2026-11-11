@props([
    'checked' => false,
    'size'    => 'normal',
])

<span class="doc-box {{ $size === 'large' ? 'doc-box--large' : '' }}">{!! $checked ? 'X' : '&#160;' !!}</span>
