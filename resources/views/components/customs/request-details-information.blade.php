@props(['rows' => []])

@if (count($rows))
    <div class="border rounded p-2">
        <div class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-2">
            <i class="fas fa-fw fa-info-circle mr-1"></i> Details Information
        </div>

        <div class="row">
            @foreach ($rows as $label => $value)
                <div class="col-12 col-md-6 mb-1">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="font-weight-medium text-break">{{ $value !== '' ? $value : 'Not recorded' }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif
