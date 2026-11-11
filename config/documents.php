<?php

/*
|--------------------------------------------------------------------------
| Printed Documents
|--------------------------------------------------------------------------
|
| Everything a paper document needs that is not a record: the paper it is
| printed on, the letterhead text and images, the widths of its fill-in blanks,
| and the spacing scale every gap in every document comes from.
|
| These are the only measurements the document views read. Changing the paper
| is a one-line change here; the mm measurements below are designed for A4
| portrait and are reported to the association for confirmation.
|
*/

return [

    // A DomPDF paper name: a4, letter or legal.
    'paper' => env('DOCUMENTS_PAPER', 'a4'),

    // The page margin, one inch on every side of the request forms (the
    // membership form, the permits, the certificate and the letters). It is
    // the margin those papers are designed with, so a sheet prints inside the
    // printable area of any printer.
    'margin' => [
        'top'    => 25.4,
        'right'  => 25.4,
        'bottom' => 25.4,
        'left'   => 25.4,
    ],

    // The one fixed height every enclosed letter shares, so the whole family
    // ends at the same place on the sheet. It is measured to clear the page
    // margin above and below the letterhead: the frame prints inside the one
    // inch the request forms leave around themselves.
    'frame_height' => 216,

    // The spacing scale, in mm. No document invents a gap outside it.
    'spacing' => [1, 2, 3, 4, 6, 8, 12],

    // Fill-in blank widths, in mm. An inline blank is always one of these.
    'widths' => [
        'short' => 12,
        'day'   => 14,
        'month' => 30,
        'year'  => 8,
        'age'   => 12,
        'name'  => 55,
        'long'  => 90,
        'date'  => 45,
        'time'  => 22,
        'block' => 12,
        'lot'   => 12,

        // The certificate and the sworn statement measure a few blanks of
        // their own: a whole name, an address, and an age written out.
        'occupant'  => 60,
        'affiant'   => 70,
        'address'   => 80,
        'age_wide'  => 20,
    ],

    // Two characters fit every 3.5 mm of a 10 pt blank.
    'chars_per_mm' => 0.57,

    'letterhead' => [
        'name'         => 'TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.',
        'address'      => [
            'BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,',
            'BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE',
        ],
        'membership_address' => 'BLOCK 20 LOT 06, PHASE 2 TRADITION HOMES,',
        'registration' => '02104',
        'contact'      => '(046) 238-1172 / 0977-749-1246',
        'email'        => 'traditionhomeshomeownersasso@gmail.com',
        'tin'          => '633-841-093-00000',
    ],

    // The images the letterhead draws, and the ones the print settings page
    // seeds its two slots with until the office uploads its own: the
    // association's logo on the left, the Hope Trece mark on the right.
    // A missing file still draws its box.
    'assets' => [
        'seal'   => 'image/logo.jpg',
        'hope'   => 'image/hope.png',
        'bagong' => 'image/documents/bagong-trece.png',
    ],

    // Where an endorsement letter goes, when the settings do not name it.
    'recipient' => [
        'name'     => '',
        'position' => 'Barangay Chairperson',
        'office'   => 'Brgy. Inocencio',
        'city'     => 'Trece Martires City, Cavite',
    ],

    // The membership form's own printed amounts, when settings do not carry
    // them (the print settings group wins).
    'membership' => [
        'fee'  => 100.00,
        'dues' => 50.00,
    ],

];
