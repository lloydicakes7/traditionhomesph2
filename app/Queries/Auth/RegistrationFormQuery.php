<?php

namespace App\Queries\Auth;

use App\Enums\CivilStatus;
use App\Enums\Sex;

/**
 * The choices the registration wizard offers: the two lists its personal step
 * asks for, and the blank form that step starts from.
 */
class RegistrationFormQuery
{
    // ===== Options =====

    /**
     * The fixed choices the wizard's selects offer.
     *
     * @return array<string, array<string, string>>
     */
    public function options(): array
    {
        return [
            'sexOptions'         => Sex::labels(),
            'civilStatusOptions' => CivilStatus::labels(),
        ];
    }

    /**
     * A blank personal form, in the resident form's own field names.
     *
     * @return array<string, mixed>
     */
    public function blankForm(): array
    {
        return [
            'surname'      => '',
            'first_name'   => '',
            'middle_name'  => '',
            'suffix'       => '',
            'mobile_no'    => '',
            'birth_date'   => '',
            'sex'          => '',
            'civil_status' => '',
        ];
    }
}
