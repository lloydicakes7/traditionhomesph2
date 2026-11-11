<?php

namespace App\Actions\Support\Complaints;

use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Resident;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class UpdateComplaint
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Complaint $complaint, array $data, Admin $by): Complaint
    {
        if ($complaint->submitted_by_homeowner) {
            throw ValidationException::withMessages([
                'form.subject' => 'A complaint a homeowner filed cannot be edited — respond to it instead.',
            ]);
        }

        $resident = Resident::findOrFail((int) $data['resident_id']);

        $complaint->update([
            'resident_id'    => $resident->id,
            'submitter_name' => $complaint->submitter_name ?: $resident->full_name,
            'category'       => $data['category'],
            'subject'        => $data['subject'],
            'description'    => $data['description'],
        ]);

        $this->activity->log(
            $by,
            'updated',
            'complaint',
            "Updated complaint \"{$complaint->subject}\"",
            ['complaint_id' => $complaint->id]
        );

        return $complaint->fresh(['resident', 'filedByAdmin', 'respondedByAdmin']);
    }
}
