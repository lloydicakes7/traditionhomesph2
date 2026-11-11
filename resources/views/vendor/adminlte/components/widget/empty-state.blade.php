<div {{ $attributes->merge(['class' => 'empty-state text-center py-4']) }}>
    @isset($icon)
        <div class="mb-3">
            <i class="{{ $icon }} fa-3x text-muted"></i>
        </div>
    @endisset

    @isset($title)
        <h6 class="font-weight-bold">{{ $title }}</h6>
    @endisset

    @isset($text)
        <p class="text-muted small mb-0">{{ $text }}</p>
    @endisset

    @isset($buttonText)
        <a href="{{ $buttonUrl ?? '#' }}" class="btn btn-primary mt-3">
            {{ $buttonText }}
        </a>
    @endisset

    {{ $slot ?? '' }}
</div>
