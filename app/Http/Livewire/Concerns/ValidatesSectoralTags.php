<?php

namespace App\Http\Livewire\Concerns;

use App\Enums\SectorType;
use App\Rules\SectoralTags;
use App\Services\Homeowners\SectoralTagService;
use Carbon\Carbon;

trait ValidatesSectoralTags
{
    /**
     * The rule that checks the sectoral tags against the person's own facts.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function sectoralTagRules(string $prefix): array
    {
        return [
            $prefix.'.sectors' => ['nullable', 'array', new SectoralTags($this->sectoralFacts($prefix))],
        ];
    }

    /**
     * The facts the tags are checked against, read from the form itself.
     *
     * @return array<string, mixed>
     */
    protected function sectoralFacts(string $prefix): array
    {
        $values = (array) ($this->{$prefix} ?? []);
        $birthDate = $values['birth_date'] ?? null;

        return [
            'age'                      => $birthDate ? Carbon::parse($birthDate)->age : null,
            'occupation'               => $values['occupation'] ?? null,
            'civil_status'             => $values['civil_status'] ?? null,
            'educational_attainment'   => $values['educational_attainment'] ?? null,
            'solo_parent_special_case' => (bool) ($values['solo_parent_special_case'] ?? false),
        ];
    }

    /**
     * The warnings the chosen tags raise without stopping the save.
     *
     * @return array<int, string>
     */
    public function sectoralWarnings(string $prefix): array
    {
        return app(SectoralTagService::class)->warnings(
            (array) ($this->{$prefix}['sectors'] ?? []),
            $this->sectoralFacts($prefix)
        );
    }

    /**
     * Keep the tags and the fields they depend on in step as the form changes.
     */
    protected function syncSectoralTags(string $prefix): void
    {
        $values = (array) ($this->{$prefix} ?? []);
        $sectors = array_values(array_unique((array) ($values['sectors'] ?? [])));

        if (in_array(SectorType::OFW, $sectors, true) && ! in_array(SectorType::LABOR_FORCE_EMPLOYED, $sectors, true)) {
            $sectors[] = SectorType::LABOR_FORCE_EMPLOYED;
        }

        if (in_array(SectorType::LABOR_FORCE_EMPLOYED, $sectors, true)) {
            $sectors = array_values(array_diff($sectors, [SectorType::UNEMPLOYED]));
        }

        if (in_array(SectorType::UNEMPLOYED, $sectors, true) && ! in_array(SectorType::LABOR_FORCE_EMPLOYED, $sectors, true)) {
            $values['occupation'] = '';
        }

        $values['sectors'] = $sectors;
        $this->{$prefix} = $values;
    }
}
