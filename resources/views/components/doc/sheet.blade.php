@props([
    'form',
    'letterhead' => [],
    'staff' => [],
    'break' => false,
    'sidebar' => true,
])

{{--
    One sheet of a printed form: the template's background art, the letterhead
    and footer the print settings own, the officers down the left, and the
    fields a document writes on top of it. Everything is placed at the
    millimetre positions `public/css/documents/print` states, so a filled sheet
    and a blank one are the same paper.

    @var string $form        the document slug, naming its art and positions
    @var array  $letterhead  print settings: header_html, left_image, right_image, footer_text, footer_image
    @var array  $staff       officers, board and photo of the president
    @var bool   $break       start this sheet on a new page
    @var bool   $sidebar     draw the officers down the left; the full-width forms do not
--}}

@php
    $background = public_path('image/documents/print/' . $form . '.png');
    $background = is_file($background) ? str_replace('\\', '/', $background) : '';
@endphp

<div class="thha-pdf-page" @if (! empty($break)) style="page-break-before: always;" @endif>
    @if ($background !== '')
        <img class="thha-pdf-bg" src="{{ $background }}" alt="">
    @endif

    <div class="thha-d-letterhead">
        <table class="thha-letterhead">
            <colgroup>
                <col style="width: 19%">
                <col style="width: 62%">
                <col style="width: 19%">
            </colgroup>
            <tr>
                <td class="thha-letterhead__mark">
                    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="24" :h="16" />
                </td>
                <td class="thha-letterhead__text">{!! $letterhead['header_html'] ?? '' !!}</td>
                <td class="thha-letterhead__mark">
                    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="24" :h="16" />
                </td>
            </tr>
        </table>
    </div>

    <div class="thha-d-footer">
        <table class="thha-footer">
            <tr>
                <td class="thha-footer__text">{!! $letterhead['footer_text'] ?? '' !!}</td>
                <td class="thha-footer__mark">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="34" :h="12" />
                </td>
            </tr>
        </table>
    </div>

    @if ($sidebar)
        <div class="thha-d-sidebar-photo">
            <x-doc.logo :src="$staff['photo'] ?? null" :w="33.51" :h="29.63" />
        </div>

        <div class="thha-d-sidebar-list">
            @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                <div class="thha-sb-name">{{ $staff[$office]['name'] ?? '' }}&#160;</div>
                <div class="thha-sb-title">{{ $staff[$office]['role'] ?? '' }}</div>
            @endforeach

            <div class="thha-sb-heading">{{ __('documents.sidebar.board') }}</div>

            @foreach (($staff['committees'] ?? []) as $committee)
                <div class="thha-sb-name">{{ $committee['name'] }}</div>
                <div class="thha-sb-title">{{ $committee['role'] }}</div>
            @endforeach
        </div>
    @endif

    {!! $slot !!}
</div>
