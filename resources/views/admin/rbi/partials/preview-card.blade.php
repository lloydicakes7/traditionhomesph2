{{-- ===== Preview ===== --}}
<div class="col-12 col-lg-8">
    <x-adminlte-card :title="__('rbi.hub.view')" icon="fas fa-fw fa-file-pdf">
        @if ($pdfUrl)
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
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-fw fa-file-pdf fa-2x mb-2"></i>
                <p class="mb-0 small">{{ __('rbi.hub.nothing_selected') }}</p>
            </div>
        @endif
    </x-adminlte-card>
</div>
