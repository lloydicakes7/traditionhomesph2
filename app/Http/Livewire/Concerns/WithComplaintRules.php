<?php

namespace App\Http\Livewire\Concerns;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use Illuminate\Validation\Rule;

trait WithComplaintRules
{
    // ===== Rules =====

    /**
     * What every complaint needs, whoever files it.
     *
     * @return array<string, array>
     */
    protected function complaintRules(): array
    {
        return [
            'form.category'          => ['required', Rule::in(ComplaintCategory::values())],
            'form.other_description' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () {
                    return ($this->form['category'] ?? null) === ComplaintCategory::OTHER;
                }),
            ],
            'form.subject'           => ['required', 'string', 'max:200'],
            'form.description'       => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * The same fields, plus who the complaint concerns — the office's filing
     * names the resident; a portal filing takes it from the account.
     *
     * @return array<string, array>
     */
    protected function complaintAdminRules(): array
    {
        return $this->complaintRules() + [
            'form.resident_id' => ['required', Rule::exists('residents', 'id')],
        ];
    }

    /**
     * What answering a complaint needs.
     *
     * @return array<string, array>
     */
    protected function complaintResponseRules(): array
    {
        return [
            'responseStatus' => ['required', Rule::in(ComplaintStatus::values())],
            'response'       => ['required', 'string', 'max:2000'],
        ];
    }
}
