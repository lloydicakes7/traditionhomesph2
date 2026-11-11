{{--
    The letterhead of every printed page. It is the print settings' own header,
    marks and rule, drawn by the one component the print documents use, so a
    record and a permit never head differently.

    @var array     $letterhead
    @var bool|null $withRule     pass false to hide the rule
--}}
<x-doc.letterhead :letterhead="$letterhead ?? []" :divider="$withRule ?? null" />
