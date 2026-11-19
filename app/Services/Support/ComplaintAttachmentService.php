<?php

namespace App\Services\Support;

use App\Models\Complaint;
use App\Utility\StorageHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Stores the files filed with a complaint.
 *
 * The admin and resident forms both upload images and documents through one
 * field, so the decision of which attachment is an image is made here from the
 * uploaded file itself, never from which input it came through.
 */
class ComplaintAttachmentService
{
    /**
     * Store every uploaded file and attach it to the complaint.
     */
    public function store(Complaint $complaint, array $attachments): void
    {
        foreach ($attachments as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $clientMime = $file->getClientMimeType();
            $mime = $file->getMimeType() ?: $clientMime;
            $extension = strtolower($file->getClientOriginalExtension());
            $isImage = Str::startsWith((string) $mime, 'image/')
                || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

            $path = StorageHelper::storePrivate($file, 'complaints/' . $complaint->id);

            $complaint->attachments()->create([
                'path'          => $path,
                'original_name' => substr($file->getClientOriginalName(), 0, 255),
                'mime_type'     => $clientMime ?: null,
                'size'          => $file->getSize(),
                'is_image'      => $isImage,
            ]);
        }
    }
}
