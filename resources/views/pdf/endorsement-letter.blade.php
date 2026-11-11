{{--
    ENDORSEMENT LETTER, printed on the association's own paper form: the
    template art carries the letter's wording and its blank lines; this view
    writes the addressee, both parties and their units, the concern and the
    signatory.

    Data: App\Queries\Documents\EndorsementLetterDocumentQuery.
--}}
<x-doc.paper form="endorsement-letter" :title="__('documents.endorsement.title')">
    <x-doc.sheet form="endorsement-letter" :letterhead="$letterhead" :staff="$staff">
        <div class="thha-pdf-field thha-f-control-no">{{ $control_no }}</div>
        <div class="thha-pdf-field thha-f-or-no">{{ $or_no }}</div>
        <div class="thha-pdf-field thha-f-date">{{ $date['long'] }}</div>
        <div class="thha-pdf-field thha-f-complainant-name">{{ $complainant_name }}</div>
        <div class="thha-pdf-field thha-f-complainant-block">{{ $complainant_block }}</div>
        <div class="thha-pdf-field thha-f-complainant-lot">{{ $complainant_lot }}</div>
        <div class="thha-pdf-field thha-f-respondent-name">{{ $respondent_name }}</div>
        <div class="thha-pdf-field thha-f-respondent-block">{{ $respondent_block }}</div>
        <div class="thha-pdf-field thha-f-respondent-lot">{{ $respondent_lot }}</div>
        <div class="thha-pdf-field thha-f-problem-1">{{ $concern_line_1 }}</div>
        <div class="thha-pdf-field thha-f-problem-2">{{ $concern_line_2 }}</div>

        <div class="thha-d-addressee-name">{{ $staff['recipient']['name'] }}</div>
        <div class="thha-d-signatory-name">{{ $staff['signer']['name'] }}</div>
        <div class="thha-d-signatory-title">{{ __('documents.endorsement.role') }}</div>
    </x-doc.sheet>
</x-doc.paper>
