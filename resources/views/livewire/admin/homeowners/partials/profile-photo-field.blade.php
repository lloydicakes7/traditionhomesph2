<div class="d-flex align-items-center flex-wrap mb-4">
    <div class="mr-3 mb-2 mb-md-0">
        @if ($photo)
            <img src="{{ $photo->temporaryUrl() }}" class="rounded-circle elevation-2"
                style="width: 80px; height: 80px; object-fit: cover;">
        @elseif ($existingUrl)
            <img src="{{ $existingUrl }}" class="rounded-circle elevation-2"
                style="width: 80px; height: 80px; object-fit: cover;">
        @else
            <img src="{{ asset('image/user.png') }}" class="rounded-circle elevation-2"
                style="width: 80px; height: 80px; object-fit: cover;">
        @endif
    </div>

    <div class="flex-grow-1" style="min-width: 220px;">
        <label class="font-weight-bold mb-0" for="{{ $domId }}">Profile Photo</label>
        <p class="text-muted small mb-2">JPG, PNG or GIF. Max 2MB.</p>

        <div class="d-flex align-items-center">
            <div class="custom-file flex-grow-1">
                <input type="file" class="custom-file-input" id="{{ $domId }}" wire:model="{{ $prop }}" accept="image/*">
                <label class="custom-file-label" for="{{ $domId }}">
                    {{ $photo ? $photo->getClientOriginalName() : 'Choose file...' }}
                </label>
            </div>
            @if ($removeAction && ($photo || $existingUrl))
                <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                    wire:click="{{ $removeAction }}" wire:loading.attr="disabled" class="btn-sm ml-2"
                    title="Remove photo" aria-label="Remove photo" />
            @endif
        </div>

        <div wire:loading wire:target="{{ $prop }}" class="mt-1">
            <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
        </div>
        @error($prop) <span class="text-danger small">{{ $message }}</span> @enderror
    </div>
</div>
