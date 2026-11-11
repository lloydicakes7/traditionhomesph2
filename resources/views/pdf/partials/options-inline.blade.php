{{--
    Checkbox options in a row.

    @var array $options  [['label' => string, 'checked' => bool,
                           'free' => bool (prints a fill-in line, optional),
                           'text' => string (its value, optional),
                           'suffix' => string (printed after the label, optional)], ...]
--}}
@foreach ($options as $o)
    <span class="opt"><span class="box">{!! ! empty($o['checked']) ? '&#10005;' : '&nbsp;' !!}</span>{{ $o['label'] }}@if (! empty($o['free'])): <span class="fill fill--sm">{{ $o['text'] ?? '' }}&nbsp;</span>@endif{{ $o['suffix'] ?? '' }}</span>
@endforeach
