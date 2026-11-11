{{--
    HOMEOWNERS ASSOCIATION PERMIT, printed on the association's own paper form:
    the template art carries the letter and its blank lines; this view writes
    who the permit is granted to, the address, the purpose, the span of the
    works and the signatory.

    Data: App\Queries\Documents\HoaPermitDocumentQuery.
--}}
<x-doc.paper form="hoa-permit" :title="__('documents.permit.title')">
    <x-doc.sheet form="hoa-permit" :letterhead="$letterhead" :staff="$staff">
        <div class="thha-pdf-field thha-f-control-no">{{ $control_no }}</div>
        <div class="thha-pdf-field thha-f-or-no">{{ $or_no }}</div>
        <div class="thha-pdf-field thha-f-granted-to">{{ $granted_to }}</div>
        <div class="thha-pdf-field thha-f-address-1">{{ $address_line_1 }}</div>
        <div class="thha-pdf-field thha-f-address-2">{{ $address_line_2 }}</div>
        <div class="thha-pdf-field thha-f-purpose-1">{{ $purpose_line_1 }}</div>
        <div class="thha-pdf-field thha-f-purpose-2">{{ $purpose_line_2 }}</div>
        <div class="thha-pdf-field thha-f-location">{{ $location }}</div>
        <div class="thha-pdf-field thha-f-event-date">{{ $event_period }}</div>
        <div class="thha-pdf-field thha-f-date-issued">{{ $issued_date['long'] }}</div>

        <div class="thha-d-signatory-name">{{ $staff['signer']['name'] }}</div>
        <div class="thha-d-signatory-title">{{ __('documents.permit.role') }}</div>
    </x-doc.sheet>
</x-doc.paper>
