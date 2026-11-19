<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A profile cannot be submitted because the resident and the unit they named
 * cannot be reconciled with the households already on file.
 *
 * The message is written for the resident, so the wizard shows it on the
 * address step instead of the generic failure notice.
 */
class ProfileSubmissionException extends RuntimeException
{
}
