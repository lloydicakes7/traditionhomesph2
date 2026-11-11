{{--
    The closing "Printed ..." line of the records that carry one.

    @var \Carbon\Carbon $printedAt
    @var string|null    $note
--}}
<div class="printed-note">
    Printed {{ $printedAt->format('M j, Y g:i A') }}@if (! empty($note)) — {{ $note }}@endif
</div>
