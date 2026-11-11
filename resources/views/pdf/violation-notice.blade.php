@extends('pdf.layouts.report')
{{--
    NOTICE OF VIOLATION — what was recorded, where and when, and what the office found.
    Builder: ViolationNoticeBuilder.

    @var array|null     $violation  null when none was chosen, else:
                                    [homeowner ("Anna Marie Lim · Blk 6 Lot 3"), status, type, occurredAt, location,
                                     issuedAt, issuedBy, acknowledgment, description (plain text),
                                     officeReport (?string), resolution (?[when, by, notes])]
    @var \Carbon\Carbon $printedAt
--}}
@section('title', 'Notice of Violation')
@section('running_header', 'Notice of Violation')

@section('content')
    <h2 class="report-title">Notice of Violation</h2>

    @if (! $violation)
        <p class="report-subtitle">No violation was chosen.</p>
    @else
        <table class="kv meta">
            <tr>
                <td class="k">Homeowner</td><td class="v">{{ $violation['homeowner'] }}</td>
                <td class="k">Status</td><td class="v">{{ $violation['status'] }}</td>
            </tr>
            <tr>
                <td class="k">Violation</td><td class="v">{{ $violation['type'] }}</td>
                <td class="k">Date &amp; Time</td><td class="v">{{ $violation['occurredAt'] }}</td>
            </tr>
            <tr>
                <td class="k">Location</td><td class="v">{{ $violation['location'] }}</td>
                <td class="k">Issued</td><td class="v">{{ $violation['issuedAt'] }}</td>
            </tr>
            <tr>
                <td class="k">Issued by</td><td class="v">{{ $violation['issuedBy'] }}</td>
                <td class="k">Acknowledgment</td><td class="v">{{ $violation['acknowledgment'] }}</td>
            </tr>
        </table>

        <div class="sect">Description</div>
        <div class="panel">{!! nl2br(e($violation['description'])) !!}</div>

        @if (! empty($violation['officeReport']))
            <div class="sect">Report to the HOA Office</div>
            <div class="panel">{!! nl2br(e($violation['officeReport'])) !!}</div>
        @endif

        @if (! empty($violation['resolution']))
            <div class="sect">Resolution</div>
            <div class="panel">
                Resolved {{ $violation['resolution']['when'] }}@if ($violation['resolution']['by']) by {{ $violation['resolution']['by'] }}@endif
                @if ($violation['resolution']['notes'])<br>{{ $violation['resolution']['notes'] }}@endif
            </div>
        @endif

        <div class="ack">
            <strong>Acknowledgment.</strong>
            Signing below confirms only that this notice was received and read. It is not an admission that the
            homeowner agrees with the violation.
        </div>

        <table class="sigs avoid-break">
            <tr>
                <td><div class="sig-line">Received by (Homeowner)</div></td>
                <td><div class="sig-line">Date received</div></td>
            </tr>
        </table>

        @include('pdf.partials.printed-note', ['note' => "this notice is part of the homeowner's permanent violation record."])
    @endif
@endsection
