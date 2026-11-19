<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The office cannot decide a profile until the household head answers the
 * resident's request to join or take over the household.
 */
class ProfileApprovalBlockedException extends RuntimeException
{
}
