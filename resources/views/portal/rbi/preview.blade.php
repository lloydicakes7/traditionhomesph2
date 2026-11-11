@extends('layouts.portal')

@section('title', $title)

@section('page-content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">{{ $title }}</h3>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-info">
                        <i class="fas fa-fw fa-print"></i> {{ __('rbi.actions.print') }}
                    </a>
                    <a href="{{ $downloadUrl }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-fw fa-download"></i> {{ __('rbi.actions.download') }}
                    </a>
                </div>
                <span class="text-muted small">{{ $title }}</span>
            </div>

            <iframe src="{{ $pdfUrl }}" class="w-100 border-0" style="height: 78vh;" title="{{ $title }}"></iframe>
        </div>
    </div>
@endsection
