<?php

namespace App\Enums;

/**
 * What a certification of residency and good standing is issued for.
 *
 * The certification form's own tick-list, so the paper form, the print page's
 * select and the PDF all read one set. Like a business permit's list, the last
 * option — "Others" — is what lets the officer write a purpose the list does
 * not carry, and it is what makes the certificate usable for anything the
 * association has not pre-printed.
 *
 * @method static CertificationPurpose RESIDENCY()
 * @method static CertificationPurpose FINANCIAL_ASSISTANCE()
 * @method static CertificationPurpose EMPLOYMENT()
 * @method static CertificationPurpose EDUCATIONAL_ASSISTANCE()
 * @method static CertificationPurpose BURIAL_ASSISTANCE()
 * @method static CertificationPurpose LOAN()
 * @method static CertificationPurpose INDIGENCY()
 * @method static CertificationPurpose MOVED_OUT()
 * @method static CertificationPurpose OTHERS()
 */
class CertificationPurpose extends Enum
{
    const RESIDENCY              = 'residency';
    const FINANCIAL_ASSISTANCE   = 'financial_assistance';
    const EMPLOYMENT             = 'employment';
    const EDUCATIONAL_ASSISTANCE = 'educational_assistance';
    const BURIAL_ASSISTANCE      = 'burial_assistance';
    const LOAN                   = 'loan';
    const INDIGENCY              = 'indigency';
    const MOVED_OUT              = 'moved_out';
    const OTHERS                 = 'others';

    /**
     * Map of value → human-readable label, in the paper form's own order.
     *
     * The certificate prints its purposes in two columns, filled left to
     * right, so the order here is the order on the paper: Residency beside
     * Financial Assistance, Employment beside Educational Assistance, and so
     * on down to Others. The select a print page offers reads the same list.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::RESIDENCY              => 'Residency',
            self::FINANCIAL_ASSISTANCE   => 'Financial Assistance',
            self::EMPLOYMENT             => 'Employment',
            self::EDUCATIONAL_ASSISTANCE => 'Educational Assistance',
            self::BURIAL_ASSISTANCE      => 'Burial Assistance',
            self::LOAN                   => 'Loan',
            self::INDIGENCY              => 'Indigency',
            self::MOVED_OUT              => 'Moved out',
            self::OTHERS                 => 'Others',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * Whether this purpose needs its own written-out text.
     */
    public function hasFreeText(): bool
    {
        return $this->value === self::OTHERS;
    }
}
