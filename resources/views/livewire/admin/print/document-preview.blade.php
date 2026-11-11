<div class="row" data-print-preview
    @if (! empty ($prefill)) data-print-prefill="{{ json_encode($prefill) }}" @endif>
    {{-- ============================================================= --}}
    {{-- PARAMETERS                                                    --}}
    {{-- ============================================================= --}}
    <div class="col-12 col-lg-5">
        <x-adminlte-card :title="$document->label()" :icon="$document->icon()">

            {{-- A walk-in resident asks for the paper at the desk: the dialog
                 opens from the right of this form's header. --}}
            @if ($document->hasBlankForm())
                @can('create', \App\Models\User::class)
                    <x-slot name="toolsSlot">
                        <x-adminlte-button label="Add Members" theme="primary" icon="fas fa-fw fa-user-plus"
                            data-toggle="modal" data-target="#homeownerFormModal" class="btn-sm" />
                    </x-slot>
                @endcan
            @endif

            @if (! empty ($prefill))
                <div class="alert alert-info border small">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>
                    Prefilled from the homeowner's request. Review the details, then print.
                </div>
            @endif

            <form data-print-form autocomplete="off" onsubmit="return false;">
                @php($today = $today ?? now()->format('Y-m-d'))

                {{-- ------------------------------------------------------ --}}
                {{-- Membership Form                                        --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::MEMBERSHIP_FORM)
                    <x-adminlte-select2 name="residentId" label="Member" igroup-size="sm"
                        wire:model="memberId">
                        <option value="">— Print a blank form —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <input type="hidden" name="requestId" value="{{ $prefill['requestId'] ?? '' }}">


                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Residents List                                         --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::RESIDENTS_LIST)
                    <x-adminlte-select2 name="addressId" label="Address" igroup-size="sm">
                        @foreach ($addresses as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select2 name="block" label="Block" igroup-size="sm">
                        <option value="">All blocks</option>
                        @foreach ($blocks as $value => $name)
                            <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select name="sortBy" label="Sort by" igroup-size="sm">
                        <option value="block">Block, then Lot</option>
                        <option value="name">Name</option>
                        <option value="type">Type</option>
                    </x-adminlte-select>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="startNo" type="number" min="1" label="From No."
                                igroup-size="sm" placeholder="first" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="endNo" type="number" min="1" label="To No."
                                igroup-size="sm" placeholder="last" />
                        </div>
                    </div>
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Occupancy List                                         --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::OCCUPANCY_LIST)
                    <x-adminlte-select name="occupantType" label="Occupants" igroup-size="sm">
                        <option value="">All occupants</option>
                        @foreach ($householdTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Business Permit                                        --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::BUSINESS_PERMIT)
                    <x-adminlte-select2 name="residentId" label="Name of Applicant" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select name="purpose" label="Purpose" igroup-size="sm"
                        data-print-reveal="#businessPurposeOther"
                        data-print-reveal-value="{{ \App\Enums\BusinessPermitPurpose::OTHER }}">
                        <option value="">— Select a purpose —</option>
                        @foreach ($permitPurposes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>

                    <div id="businessPurposeOther" class="d-none">
                        <x-adminlte-input name="purposeOther" label="Other Purpose" igroup-size="sm" />
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="controlNumber" label="Control No." igroup-size="sm"
                                placeholder="left blank prints a fill-in line" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="issuedAt" type="date" label="Date Issued" value="{{ $today }}"
                                igroup-size="sm" />
                        </div>
                    </div>
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- HOA Permit                                             --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::HOA_PERMIT)
                    <x-adminlte-select2 name="residentId" label="Permit is hereby granted to" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-textarea name="purpose" label="Purpose" rows="2" igroup-size="sm"
                        placeholder="e.g. Roof repair and interior repainting" />

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="contractor" label="Contractor (optional)" igroup-size="sm"
                                placeholder="e.g. JM Builders" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="workers" type="number" min="1" label="No. of Workers"
                                igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="location" label="Location" igroup-size="sm"
                        placeholder="the unit address, unless the work is elsewhere" />

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="eventFrom" type="date" label="Date of event" value="{{ $today }}"
                                igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="eventTo" type="date" label="Until (optional)" igroup-size="sm" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="controlNumber" label="Control No." igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="orNumber" label="O.R. No." igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="issuedAt" type="date" label="Date Issued" value="{{ $today }}"
                        igroup-size="sm" />
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Endorsement Letter                                     --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::ENDORSEMENT_LETTER)
                    <x-adminlte-select2 name="residentId" label="Complainant" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select2 name="respondentId" label="Complained of" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-input name="addresseeName" label="Addressee" igroup-size="sm"
                        placeholder="Hon. Rosendo Dilidili" />
                    <x-adminlte-input name="addresseePosition" label="Position" igroup-size="sm"
                        placeholder="Barangay Chairperson" />
                    <x-adminlte-input name="addresseeOffice" label="Office" igroup-size="sm"
                        placeholder="Brgy. Inocencio" />
                    <x-adminlte-input name="addresseeCity" label="City" igroup-size="sm"
                        placeholder="Trece Martires City, Cavite" />

                    <x-adminlte-textarea name="problem" label="The problem is with regards to" rows="2"
                        igroup-size="sm" placeholder="excessive noise (videoke) beyond quiet hours" />

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="mediationAttempted"
                            name="mediationAttempted" value="1" checked>
                        <label class="custom-control-label" for="mediationAttempted">
                            Mediation was attempted

                            <br><small>They were already invited to the Homeowners Office, to no avail</small>
                        </label>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="controlNumber" label="Control No." igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="orNumber" label="O.R. No." igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="letterDate" type="date" label="Date" value="{{ $today }}"
                        igroup-size="sm" />

                    {{-- The respondent's unit, when the letter is being printed from a
                         homeowner's request: the request carries the household's block
                         and lot, and these keep them in the form's own query. --}}
                    <input type="hidden" name="respondentBlock" value="">
                    <input type="hidden" name="respondentLot" value="">
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Certification                                          --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::CERTIFICATION)
                    <x-adminlte-select2 name="residentId" label="Occupant" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select name="purpose" label="Purpose" igroup-size="sm"
                        data-print-reveal="#certificationPurposeOther"
                        data-print-reveal-value="{{ \App\Enums\CertificationPurpose::OTHERS }}">
                        <option value="">— Select a purpose —</option>
                        @foreach ($certificationPurposes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>

                    <div id="certificationPurposeOther" class="d-none">
                        <x-adminlte-input name="purposeOther" label="Other Purpose" igroup-size="sm" />
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="controlNumber" label="Control No." igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="orNumber" label="O.R. No." igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="issuedAt" type="date" label="Date Issued" value="{{ $today }}"
                        igroup-size="sm" />
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Invitation Letter                                      --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::INVITATION_LETTER)
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="inviteAll" name="allHomeowners"
                            value="1" data-print-hide="#inviteResidentField">
                        <label class="custom-control-label" for="inviteAll">
                            All homeowners
                            <br><small>one sheet per household head with an account</small>
                        </label>
                    </div>

                    <div id="inviteResidentField">
                        <x-adminlte-select2 name="residentId" label="Recipient" igroup-size="sm">
                            <option value="">— Select a resident —</option>
                            @foreach ($residents as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>

                    <x-adminlte-input name="eventTitle" label="The meeting is called" igroup-size="sm"
                        placeholder="meeting — printed &quot;a meeting&quot;" />

                    <x-adminlte-textarea name="concern" label="With regards to" rows="2" igroup-size="sm"
                        placeholder="a concern that needs to fix" />

                    <x-adminlte-input name="venue" label="Venue (optional)" igroup-size="sm"
                        placeholder="the Homeowners Association's Office, unless elsewhere" />

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="meetingDate" type="date" label="Date" value="{{ $today }}"
                                igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="meetingTime" type="time" label="Time" value="15:00"
                                igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="letterDate" type="date" label="Date of the letter" value="{{ $today }}"
                        igroup-size="sm" />
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Sinumpaang Salaysay                                    --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::SINUMPAANG_SALAYSAY)
                    <x-adminlte-select2 name="residentId" label="Affiant (Ako si)" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select2 name="respondentId" label="Nirereklamo (respondent)" igroup-size="sm">
                        <option value="">— Select a resident —</option>
                        @foreach ($residents as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="caseNumber" label="Case #" igroup-size="sm"
                                placeholder="THHA-2026-014" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="caseTitle" label="Case" igroup-size="sm"
                                placeholder="Noise complaint" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <x-adminlte-input name="incidentDate" type="date" label="Incident Date"
                                value="{{ $today }}" igroup-size="sm" />
                        </div>
                        <div class="col-6">
                            <x-adminlte-input name="incidentTime" type="time" label="Incident Time" value="21:30"
                                igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-textarea name="narrative" label="Ang pangyayari — one paragraph per line" rows="6"
                        igroup-size="sm" />

                    <x-adminlte-input name="executionDate" type="date" label="Nilagdaan noong" value="{{ $today }}"
                        igroup-size="sm" />
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Journal Voucher                                        --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::JOURNAL_VOUCHER)
                    <x-adminlte-select2 name="journalEntryId" label="Journal Entry" igroup-size="sm">
                        <option value="">— Select an entry —</option>
                        @foreach ($journalEntries as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Violation Notice                                       --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::VIOLATION_NOTICE)
                    <x-adminlte-select2 name="violationId" label="Violation" igroup-size="sm">
                        <option value="">— Select a violation —</option>
                        @foreach ($violations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select2>
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Financial Report                                       --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::FINANCIAL_REPORT)
                    <x-adminlte-select name="periodType" label="Period" igroup-size="sm">
                        <option value="month">A month</option>
                        <option value="day">A day</option>
                        <option value="year">A year</option>
                    </x-adminlte-select>

                    <x-adminlte-input name="periodDate" type="date" label="Date in the period"
                        value="{{ $today }}" igroup-size="sm" />
                @endif

                {{-- ------------------------------------------------------ --}}
                {{-- Summary Report                                         --}}
                {{-- ------------------------------------------------------ --}}
                @if ($document->value === \App\Enums\PrintDocument::SUMMARY_REPORT)
                    <x-adminlte-select name="accountType" label="Rows" igroup-size="sm">
                        <option value="{{ \App\Enums\AccountType::INCOME }}">
                            {{ \App\Enums\AccountType::INCOME()->label() }}
                        </option>
                        <option value="{{ \App\Enums\AccountType::EXPENSE }}">
                            {{ \App\Enums\AccountType::EXPENSE()->label() }}
                        </option>
                    </x-adminlte-select>

                    <x-adminlte-select name="year" label="Year" igroup-size="sm">
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-adminlte-select>
                @endif
            </form>

            <x-slot name="footerSlot">
                <div class="text-right">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-print-clear>
                        <i class="fas fa-fw fa-eraser"></i> Clear
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-print-update>
                        <i class="fas fa-fw fa-sync-alt"></i> Update Preview
                    </button>
                </div>
            </x-slot>
        </x-adminlte-card>
    </div>

    {{-- ============================================================= --}}
    {{-- PREVIEW                                                        --}}
    {{-- ============================================================= --}}
    <div class="col-12 col-lg-7">
        <x-adminlte-card title="Preview" icon="fas fa-fw fa-file-pdf">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    @can('print.export')
                        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                            data-print-link="{{ $pdfUrl }}" class="btn btn-sm btn-info">
                            <i class="fas fa-fw fa-print"></i> Print
                        </a>
                        <a href="{{ $pdfUrl }}?download=1"
                            data-print-link="{{ $pdfUrl }}?download=1"
                            data-thha-progress-download="Preparing the PDF download…"
                            class="btn btn-sm btn-secondary">
                            <i class="fas fa-fw fa-download"></i> Download
                        </a>
                        @if ($document->hasBlankForm())
                            <a href="{{ $pdfUrl }}?blank=1" target="_blank" rel="noopener"
                                data-print-link="{{ $pdfUrl }}?blank=1" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-fw fa-file"></i> {{ __('documents.errors.blank') }}
                            </a>
                        @endif
                    @endcan
                </div>
                <span class="text-muted small">{{ $document->label() }}</span>
            </div>

            <div class="position-relative border"
                style="width:100%; aspect-ratio: {{ $document->paperRatio() }}; max-height: 87vh; overflow: hidden;">
                <iframe data-print-frame data-print-src="{{ $pdfUrl }}" src="{{ $pdfUrl }}"
                    style="width:100%; height:100%; border:0;" title="{{ $document->label() }}"></iframe>
            </div>
        </x-adminlte-card>
    </div>
</div>
