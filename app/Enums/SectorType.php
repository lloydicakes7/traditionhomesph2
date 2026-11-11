<?php

namespace App\Enums;

/**
 * @method static SectorType LABOR_FORCE_EMPLOYED()
 * @method static SectorType UNEMPLOYED()
 * @method static SectorType OFW()
 * @method static SectorType PWD()
 * @method static SectorType OSC()
 * @method static SectorType OSY()
 * @method static SectorType SOLO_PARENT()
 * @method static SectorType INDIGENOUS_PEOPLE()
 * @method static SectorType MIGRANT()
 * @method static SectorType SENIOR_CITIZEN()
 */
class SectorType extends Enum
{
    const LABOR_FORCE_EMPLOYED = 'labor_force_employed';
    const UNEMPLOYED           = 'unemployed';
    const OFW                  = 'ofw';
    const PWD                  = 'pwd';
    const OSC                  = 'osc';
    const OSY                  = 'osy';
    const SOLO_PARENT          = 'solo_parent';
    const INDIGENOUS_PEOPLE    = 'indigenous_people';
    const MIGRANT              = 'migrant';
    const SENIOR_CITIZEN       = 'senior_citizen';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::LABOR_FORCE_EMPLOYED => 'Labor Force / Employed',
            self::UNEMPLOYED           => 'Unemployed',
            self::OFW                  => 'Overseas Filipino Worker (OFW)',
            self::PWD                  => 'Person with Disability (PWD)',
            self::OSC                  => 'Out of School Children (OSC)',
            self::OSY                  => 'Out of School Youth (OSY)',
            self::SOLO_PARENT          => 'Solo Parent',
            self::INDIGENOUS_PEOPLE    => 'Indigenous People (IP)',
            self::MIGRANT              => 'Migrant',
            self::SENIOR_CITIZEN       => 'Senior Citizen (SC)',
        ];
    }

    /**
     * The labels the RBI forms print for their sector rows and columns.
     *
     * @return array<string, string>
     */
    public static function reportLabels(): array
    {
        return [
            self::LABOR_FORCE_EMPLOYED => 'Labor Force',
            self::UNEMPLOYED           => 'Unemployed',
            self::OSC                  => 'Out of School Children (OSC) (6-14 years old)',
            self::OSY                  => 'Out of School Youth (OSY) (15-24 years old)',
            self::PWD                  => 'Person with Disabilities (PWDs)',
            self::OFW                  => 'Overseas Filipino Workers (OFWs)',
            self::SOLO_PARENT          => 'Solo Parents',
            self::INDIGENOUS_PEOPLE    => 'Indigenous Peoples (IPs)',
        ];
    }

    /**
     * The short label Form A writes in its indicator column.
     */
    public static function indicatorLabel(string $sector): string
    {
        return [
            self::LABOR_FORCE_EMPLOYED => 'Labor/employed',
            self::UNEMPLOYED           => 'Unemployed',
            self::PWD                  => 'PWD',
            self::OFW                  => 'OFW',
            self::SOLO_PARENT          => 'Solo Parent',
            self::OSY                  => 'OSY',
            self::OSC                  => 'OSC',
            self::INDIGENOUS_PEOPLE    => 'IP',
        ][$sector] ?? '';
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The ages the sector is limited to, or null when it is not.
     *
     * @return array{0: int, 1: int}|null
     */
    public function ageRange(): ?array
    {
        if ($this->value === self::OSC) {
            return [6, 14];
        }

        if ($this->value === self::OSY) {
            return [15, 24];
        }

        return null;
    }

    /**
     * Whether a resident of this age may be tagged with the sector.
     */
    public function appliesToAge(?int $age): bool
    {
        $range = $this->ageRange();

        if ($range === null) {
            return true;
        }

        return $age !== null && $age >= $range[0] && $age <= $range[1];
    }
}
