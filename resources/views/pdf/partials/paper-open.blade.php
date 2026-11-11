{{--
    Opens one framed paper page: letterhead, then the frame with the officers
    (and control lines) on the left and an open content cell on the right.
    Always paired with partials/paper-close. Split in two so a document that
    prints several pages (the invitation letter) can repeat the frame.

    @var bool|null $break   start this page on a new sheet
--}}
<div class="page {{ ! empty($break) ? 'page-break' : '' }}">
    @include('pdf.partials.letterhead', ['withRule' => false])

    <table class="frame">
        <tr>
            <td class="frame-side">
                @include('pdf.partials.sidebar')
                @include('pdf.partials.control')
            </td>
            <td class="frame-main body-text">
