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

    public function handle(Complaint $complaint, array $data, ?Admin $by = null): Complaint
    {
        if ($by === null && ! $complaint->can_be_changed_by_homeowner) {
            throw ValidationException::withMessages([
                'form.subject' => 'This complaint can no longer be edited: the office has already acted on it.',
            ]);
        }

        if ($by !== null && $complaint->submitted_by_homeowner) {
            throw ValidationException::withMessages([
                'form.subject' => 'A complaint a homeowner filed cannot be edited: respond to it instead.',
            ]);
        }

        $attributes = [
            'category'          => $data['category'],
            'other_description' => Complaint::otherDescriptionFrom($data),
            'subject'           => $data['subject'],
            'description'       => $data['description'],
        ];

        if ($by !== null) {
            $resident = Resident::findOrFail((int) $data['resident_id']);

            $attributes['resident_id'] = $resident->id;
            $attributes['submitter_name'] = $complaint->submitter_name ?: $resident->full_name;
        }

        $complaint->update($attributes);

        if ($by !== null) {
            $this->activity->log(
                $by,
                'updated',
                'complaint',
                "Updated complaint \"{$complaint->subject}\"",
                ['complaint_id' => $complaint->id]
            );
        }

        return $complaint->fresh(['resident', 'filedByAdmin', 'respondedByAdmin']);
    }
}
