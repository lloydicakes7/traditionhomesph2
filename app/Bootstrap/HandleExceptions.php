<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

class HandleExceptions extends BaseHandleExceptions
{
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED], true)) {
            return;
        }

        parent::handleError($level, $message, $file, $line, $context);
    }
}
