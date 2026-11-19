@props([
    'title',
    'meta' => null,
    'metaIcon' => 'far fa-fw fa-calendar-alt',
    'statusTheme' => 'secondary',
    'statusLabel' => null,
    'extraBadge' => null,
    'extraBadgeTheme' => 'warning',
    'extraBadgeIcon' => null,
    'details' => [],
])

<div class="card d-flex flex-column mb-0 shadow-sm">
    <div class="card-header bg-white px-3 py-2">
        <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
            <div class="min-width-0">
                <div class="font-weight-bold text-break">{{ $title }}</div>
                @if ($meta)
                    <small class="text-muted">
                        <i class="{{ $metaIcon }} mr-1"></i>{{ $meta }}
                    </small>
                @endif
            </div>
            <div class="text-right flex-shrink-0">
                @if ($statusLabel)
                    <span class="badge badge-{{ $statusTheme }}">{{ $statusLabel }}</span>
                @endif

                @if ($extraBadge)
                    <div class="mt-1">
                        <span class="badge badge-{{ $extraBadgeTheme }}">
                            @if ($extraBadgeIcon)<i class="{{ $extraBadgeIcon }}"></i> @endif{{ $extraBadge }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body d-flex flex-column flex-grow-1 px-3 py-3">
        @foreach ($details as $label => $value)
            @if ($value !== null && $value !== '')
                <div class="d-flex justify-content-between align-items-start mb-1" style="gap:0.75rem;">
                    <div class="text-muted small flex-shrink-0">{{ $label }}</div>
                    <div class="small font-weight-medium text-right text-break">{{ $value }}</div>
                </div>
            @endif
        @endforeach

        {{ $slot }}

        @isset($actions)
            <div class="mt-auto">
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex flex-wrap justify-content-end align-items-center" style="gap:0.5rem;">
                        {{ $actions }}
                    </div>
                </div>
            </div>
        @endisset
    </div>
</div>
