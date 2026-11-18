<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\Storage;

/**
 * The one way a complaint's private evidence is read back.
 *
 * Files live on the private disk and have no public URL. An officer with the
 * Complaints view permission opens or downloads them here; a request for an
 * attachment that belongs to another complaint is refused as missing.
 */
class ComplaintAttachmentController extends Controller
{
    public function show(Complaint $complaint, ComplaintAttachment $attachment)
    {
        $this->authorize('view', $complaint);

        abort_unless((int) $attachment->complaint_id === (int) $complaint->id, 404);
        abort_unless(StorageHelper::privateExists($attachment->path), 404);

        if (request()->boolean('inline') && $attachment->is_image) {
            return Storage::disk('local')->response($attachment->path, $attachment->original_name);
        }

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
