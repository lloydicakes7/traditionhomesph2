{{--
    The Control No. / O.R. No. lines under the officers. Left empty they print
    as blank lines for the office to write on.

    @var string|null $controlNumber
    @var string|null $orNumber
    @var string|null $controlLabel   default "Control No."
    @var bool|null   $showOr         default true
--}}
<div class="control">
    {{ $controlLabel ?? 'Control No.' }} <span class="fill">{{ $controlNumber ?? '' }}&nbsp;</span>
    @if ($showOr ?? true)
        <br>O.R. No. <span class="fill">{{ $orNumber ?? '' }}&nbsp;</span>
    @endif
</div>
