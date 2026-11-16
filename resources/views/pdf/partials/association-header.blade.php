{{--
    The association's heading, as the framed letters print it: the marks at
    either side and the fixed lines down the middle. The text is the same on
    every copy of the paper, so it is stated once here.

    @var array $letterhead  the print settings' letterhead
--}}
@php
    $lines = [
        ['top' => 20.489, 'size' => 11.04, 'bold' => true, 'text' => 'TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.'],
        ['top' => 34.724, 'size' => 8.04, 'bold' => true, 'text' => 'BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,'],
        ['top' => 45.404, 'size' => 8.04, 'bold' => true, 'text' => 'BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE'],
        ['top' => 56.084, 'size' => 8.04, 'bold' => true, 'text' => 'HLURB REGISTRATION # 02104'],
        ['top' => 66.664, 'size' => 8.04, 'bold' => true, 'text' => 'CONTACT NO. (046) 238-1172 / 0977-749-1246'],
        ['top' => 77.344, 'size' => 8.04, 'bold' => false, 'text' => 'Email: traditionhomeshomeownersasso@gmail.com'],
        ['top' => 88.024, 'size' => 8.04, 'bold' => false, 'text' => 'Non-Vat Registration TIN#633-841-093-00000'],
    ];
@endphp

<div class="thha-fl-mark thha-fl-mark--left">
    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="23.81" :h="23.81" />
</div>

<div class="thha-fl-mark thha-fl-mark--right">
    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="32.6" :h="16.3" />
</div>

@foreach ($lines as $line)
    <div class="thha-fl-headline" style="top: {{ $line['top'] }}pt; font-size: {{ $line['size'] }}pt; font-weight: {{ $line['bold'] ? 'bold' : 'normal' }};">{{ $line['text'] }}</div>
@endforeach

<div class="thha-fl-tin">Non-VAT Registration TIN#633-841-093-00000</div>
