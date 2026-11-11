{{--
    Checkbox options in two columns (the paper forms' purpose lists).
    Same option shape as options-inline.

    @var array $options
--}}
<table class="opts">
    @foreach (array_chunk($options, 2) as $row)
        <tr>
            @foreach ($row as $o)
                <td><span class="box">{!! ! empty($o['checked']) ? '&#10005;' : '&nbsp;' !!}</span>{{ $o['label'] }}@if (! empty($o['free'])): <span class="fill fill--md">{{ $o['text'] ?? '' }}&nbsp;</span>@endif</td>
            @endforeach
            @if (count($row) === 1)
                <td></td>
            @endif
        </tr>
    @endforeach
</table>
