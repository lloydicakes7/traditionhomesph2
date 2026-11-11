@props(['message' => 'Loading…'])

<div class="position-relative py-5 d-flex flex-column justify-content-center align-items-center">
    <i class="fas fa-2x fa-sync fa-spin mb-2"></i>
    <p class="text-center text-muted small">{{ $message }}</p>
</div>
