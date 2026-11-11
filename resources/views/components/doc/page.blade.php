@props(['title' => 'Document'])

@php
    $sheets = ['base.css', 'letterhead.css', 'frame.css', 'forms.css', 'membership.css'];
    $styles = '';

    foreach ($sheets as $sheet) {
        $styles .= \App\Utility\PdfBuilder::stylesheet('css/documents/' . $sheet) . "\n";
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: {{ config('documents.paper') }} portrait;
            margin: {{ config('documents.margin.top') }}mm {{ config('documents.margin.right') }}mm {{ config('documents.margin.bottom') }}mm {{ config('documents.margin.left') }}mm;
        }
        {!! $styles !!}
    </style>
</head>
<body>
    <div class="doc-sheet">
        {!! $slot !!}
    </div>
</body>
</html>
