@props([
    'attachments' => [],
    'inputId' => 'complaintAttachments',
])

<div>
    <label for="{{ $inputId }}" class="font-weight-bold">Attachments</label>
    <div class="custom-file">
        <input type="file" class="custom-file-input" id="{{ $inputId }}"
            wire:model="attachments" multiple
            accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv">
        <label class="custom-file-label" for="{{ $inputId }}">Choose images or files...</label>
    </div>
    <small class="text-muted">
        JPG, JPEG, PNG, WEBP, PDF, Word, Excel, text or CSV. Up to 5 attachments;
        images up to 5 MB, documents up to 10 MB.
    </small>

    <div wire:loading wire:target="attachments" class="mt-1">
        <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading attachments...</small>
    </div>

    @error('attachments') <span class="text-danger small d-block">{{ $message }}</span> @enderror
    @error('attachments.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror

    @if (! empty($attachments))
        <ul class="list-group list-group-flush mt-2">
            @foreach ($attachments as $index => $attachment)
                @php
                    $extension = strtolower($attachment->getClientOriginalExtension());
                    $isImage = \Illuminate\Support\Str::startsWith(
                        (string) ($attachment->getMimeType() ?: $attachment->getClientMimeType()),
                        'image/'
                    ) || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
                    $icon = $isImage
                        ? 'fa-file-image'
                        : ([
                            'pdf'  => 'fa-file-pdf',
                            'doc'  => 'fa-file-word',
                            'docx' => 'fa-file-word',
                            'xls'  => 'fa-file-excel',
                            'xlsx' => 'fa-file-excel',
                            'csv'  => 'fa-file-csv',
                            'txt'  => 'fa-file-alt',
                        ][$extension] ?? 'fa-file-alt');
                @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2"
                    wire:key="complaint-attachment-{{ $index }}">
                    <span class="small text-truncate pr-2">
                        <i class="fas fa-fw {{ $icon }} text-muted mr-1"></i>
                        {{ $attachment->getClientOriginalName() }}
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-danger"
                        wire:click="removeAttachment({{ $index }})">
                        Remove
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
