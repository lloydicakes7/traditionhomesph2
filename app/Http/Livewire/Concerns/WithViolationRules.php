<?php

namespace App\Http\Livewire\Concerns;

use App\Enums\ViolationType;
use Illuminate\Validation\Rule;

trait WithViolationRules
{
    // ===== Rules =====

    /**
     * What a violation needs, whoever is writing one down.
     *
     * @return array<string, array>
     */
    protected function violationRules(): array
    {
        return [
            'form.resident_id'    => ['required', Rule::exists('residents', 'id')],
            'form.violation_type' => ['required', Rule::in(ViolationType::values())],
            'form.occurred_on'    => ['required', 'date_format:Y-m-d'],
            'form.occurred_time'  => ['required', 'date_format:H:i'],
            'form.location'       => ['required', 'string', 'max:255'],
            'form.description'    => ['required', 'string', 'max:2000'],
            'form.office_report'  => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * What resolving a violation needs — the notes are optional.
     *
     * @return array<string, array>
     */
    protected function violationResolveRules(): array
    {
        return [
            'resolutionNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
