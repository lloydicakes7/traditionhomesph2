@props([
    'letterhead' => [],
])

{{-- The enclosed letter: the officers down the left, the letter on the right,
     the association's footer under it. A table with no measured height and no
     positioned boxes: dompdf draws a positioned box against the page edge and
     splits a fixed height across sheets, either of which would print the letter
     outside the page margin. --}}
<table class="doc-frame">
    <tr>
        <td class="doc-frame__sidebar">{!! $sidebar !!}</td>
        <td class="doc-frame__main">
            {!! $slot !!}

            <div class="doc-gap-6"></div>

            <x-doc.footer :letterhead="$letterhead" />
        </td>
    </tr>
</table>
