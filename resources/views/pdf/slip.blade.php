@extends('pdf.layouts.report')

@section('title', __('slips.title') . ' ' . $slip['reference'])
@section('running_header', __('slips.title') . ' · ' . $slip['reference'])

@section('styles')
    <style>{!! $styles !!}</style>
@endsection

@section('content')
    {{-- ===== Header ===== --}}
    <table class="slip-head">
        <tr>
            <td>
                <div class="slip-title">{{ __('slips.title') }}</div>
                <div class="slip-subtitle">{{ $slip['kind'] }}</div>
            </td>
            <td class="slip-head-meta">
                <table class="kv meta slip-meta">
                    <tr>
                        <td class="k">{{ __('slips.reference') }}</td>
                        <td class="v">{{ $slip['reference'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== Who and when ===== --}}
    <table class="kv slip-parties">
        <tr>
            <td class="k">{{ __('slips.homeowner') }}</td>
            <td class="v">{{ $slip['homeowner'] }}</td>
            <td class="k">{{ __('slips.unit') }}</td>
            <td class="v">{{ $slip['unit'] }}</td>
        </tr>
        <tr>
            <td class="k">{{ __('slips.filed_at') }}</td>
            <td class="v">{{ $slip['filed_at'] }}</td>
            <td class="k">{{ __('slips.status') }}</td>
            <td class="v">
                <span class="slip-status slip-status-{{ $slip['status_theme'] }}">{{ $slip['status'] }}</span>
            </td>
        </tr>
    </table>

    {{-- ===== What was filed ===== --}}
    <div class="sect">{{ __('slips.details') }}</div>

    <table class="kv slip-details">
        @foreach ($slip['rows'] as $row)
            <tr>
                <td class="k">{{ $row['label'] }}</td>
                <td class="v">{{ $row['value'] }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ===== What the slip is for ===== --}}
    <div class="slip-note">
        <div class="slip-note-line">{{ $slip['note'] }}</div>
        <div class="slip-note-strong">{{ __('slips.printable') }}</div>
    </div>

    @include('pdf.partials.printed-note')
@endsection
