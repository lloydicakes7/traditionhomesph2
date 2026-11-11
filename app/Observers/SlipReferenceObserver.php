<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Numbers a slip-bearing record as it is created.
 *
 * A request and a booking both file under a reference the homeowner brings to
 * the office, so neither may exist without one. Writing it here means every
 * path that files one — the portal's own forms and the office filing on a
 * resident's behalf — gets the same number without repeating the call, and
 * nothing else has to remember to ask for it.
 */
class SlipReferenceObserver
{
    /**
     * Write the record's reference when it has none yet.
     */
    public function creating(Model $model): void
    {
        if (trim((string) $model->reference) !== '') {
            return;
        }

        $model->reference = $model::nextReference();
    }
}
