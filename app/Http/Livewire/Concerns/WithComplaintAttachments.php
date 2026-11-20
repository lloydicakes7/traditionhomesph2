<?php

namespace App\Http\Livewire\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

/**
 * One attachment field for a complaint: images and documents together.
 *
 * The file types are the union both forms already accepted. Documents keep the
 * existing 10 MB limit; images keep the existing 5 MB limit, checked here so
 * the merged field does not quietly relax it.
 */
trait WithComplaintAttachments
{
    use WithFileUploads;

    /** @var array<int, \Livewire\TemporaryUploadedFile> */
    public $attachments = [];

    /**
     * Rules for the combined attachment list.
     *
     * @return array<string, array>
     */
    protected function complaintAttachmentRules(): array
    {
        return [
            'attachments'   => ['array', 'max:5'],
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $mime = (string) ($value->getMimeType() ?: $value->getClientMimeType());
                    $extension = strtolower($value->getClientOriginalExtension());
                    $isImage = Str::startsWith($mime, 'image/')
                        || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

                    if ($isImage && $value->getSize() > 5120 * 1024) {
                        $fail('Images may not be larger than 5 MB.');
                    }
                },
            ],
        ];
    }

    /**
     * Remove one selected attachment before the complaint is submitted.
     */
    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }
}
