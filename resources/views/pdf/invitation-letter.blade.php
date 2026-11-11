{{--
    INVITATION LETTER, printed on the association's own paper form: one sheet
    per recipient, the template art carrying the wording and the blank lines
    this view writes the addressee, the meeting and the signatory onto.

    Data: App\Queries\Documents\InvitationLetterDocumentQuery.
--}}
<x-doc.paper form="invitation-letter" :title="__('documents.invitation.title')">
    @foreach ($recipients as $index => $recipient)
        <x-doc.sheet form="invitation-letter" :letterhead="$letterhead" :staff="$staff" :break="$index > 0">
            <div class="thha-pdf-field thha-f-control-no">{{ $control_no }}</div>
            <div class="thha-pdf-field thha-f-or-no">{{ $or_no }}</div>
            <div class="thha-pdf-field thha-f-date">{{ $date['long'] }}</div>
            <div class="thha-pdf-field thha-f-to-name">{{ $recipient['name'] }}</div>
            <div class="thha-pdf-field thha-f-address-1">{{ $recipient['address_1'] }}</div>
            <div class="thha-pdf-field thha-f-address-2">{{ $recipient['address_2'] }}</div>
            <div class="thha-pdf-field thha-f-meeting-date">{{ $meeting_date['long'] }}</div>
            <div class="thha-pdf-field thha-f-meeting-time">{{ $meeting_time }}</div>

            <div class="thha-d-signatory-name">{{ $staff['signer']['name'] }}</div>
            <div class="thha-d-signatory-title">{{ __('documents.invitation.role') }}</div>
        </x-doc.sheet>
    @endforeach
</x-doc.paper>
