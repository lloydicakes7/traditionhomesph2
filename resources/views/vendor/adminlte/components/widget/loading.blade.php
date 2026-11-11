<div {{ $attributes->merge(['class' => 'loading-skeleton py-3']) }}>
    @if($type === 'spinner')
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    @else
        @foreach($lineWidths() as $width)
            <div class="skeleton-line mb-2" style="width:{{ $width }}%;height:14px;border-radius:5px;background:linear-gradient(90deg, var(--border-soft) 25%, var(--cream) 37%, var(--border-soft) 63%);background-size:400% 100%;animation: shimmer 1.6s infinite linear;"></div>
        @endforeach
    @endif
</div>

@once
@push('css')
<style>
@keyframes shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
</style>
@endpush
@endonce
