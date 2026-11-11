@props(['value' => null])

@if (filled($value))
    {{ $value }}
@else
    <span class="text-muted">{{ __('common.not_provided') }}</span>
@endif
