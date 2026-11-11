{{--
    The footer of every printed page: the print settings' own line and brand
    image, drawn by the one component the print documents use.

    @var array $letterhead
--}}
<x-doc.footer :letterhead="$letterhead ?? []" />
