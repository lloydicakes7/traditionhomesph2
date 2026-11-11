@props([
    'form',
    'title' => 'Document',
])

{{--
    A document printed on the association's own paper: the sheet art the office
    keeps in `public/image/documents/print`, with the form's field positions from
    `public/css/documents/print`. A child lays its sheets out with
    `x-doc.sheet`; this part only heads the document and loads the paper.

    @var string $form   the document slug: business-permit, certification, …
    @var string $title  the document's name, as the PDF metadata reads it
--}}

@php
    $styles = '';

    foreach (['chrome', $form] as $sheet) {
        $styles .= \App\Utility\PdfBuilder::stylesheet('css/documents/print/' . $sheet . '.css') . "\n";
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>{!! $styles !!}</style>
</head>
<body>
    {!! $slot !!}
</body>
</html>
