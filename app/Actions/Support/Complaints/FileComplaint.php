<?php

namespace App\Actions\Support\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintFiledNotification;
use App\Services\NotificationService;
use App\Utility\StorageHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FileComplaint
{
    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(array $data, User $account, array $images = [], array $files = []): Complaint
    {
        $resident = $account->resident;

        $complaint = DB::transaction(function () use ($data, $account, $resident, $images, $files) {
            $complaint = Complaint::create([
                'resident_id'       => $account->resident_id,
                'submitter_name'    => $resident->full_name ?? null,
                'category'          => $data['category'],
                'other_description' => Complaint::otherDescriptionFrom($data),
                'subject'           => $data['subject'],
                'description'       => $data['description'],
                'status'            => ComplaintStatus::PENDING,
            ]);

            $this->storeAttachments($complaint, $images, $files);

            return $complaint;
        });

        $complaint = $complaint->fresh('resident');

        $this->notifications->toOfficers(new ComplaintFiledNotification($complaint));

        return $complaint;
    }

    // ===== Attachments =====

    private function storeAttachments(Complaint $complaint, array $images, array $files): void
    {
        foreach ($images as $image) {
            $this->storeAttachment($complaint, $image, true);
        }

        foreach ($files as $file) {
            $this->storeAttachment($complaint, $file, false);
        }
    }

    private function storeAttachment(Complaint $complaint, UploadedFile $file, bool $isImage): void
    {
        $path = StorageHelper::storePrivate($file, 'complaints/' . $complaint->id);

        $complaint->attachments()->create([
            'path'          => $path,
            'original_name' => substr($file->getClientOriginalName(), 0, 255),
            'mime_type'     => $file->getClientMimeType() ?: null,
            'size'          => $file->getSize(),
            'is_image'      => $isImage,
        ]);
    }
}
