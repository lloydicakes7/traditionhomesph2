<?php

namespace App\Http\Livewire\Portal\Rbi;

use App\Http\Livewire\PortalComponent;
use Illuminate\Support\Facades\Auth;

class MyRbi extends PortalComponent
{
    public function render()
    {
        $user = Auth::guard('homeowner')->user();
        $household = $user ? $user->household : null;

        if ($household !== null) {
            $this->authorize('printRbiOwn', $household);

            $household->load('user.resident', 'property', 'memberRecords.resident');
        }

        return view('livewire.portal.rbi.my-rbi', [
            'household' => $household,
            'head' => $household ? $household->head_resident : null,
            'members' => $household ? $household->memberRecords : collect(),
        ]);
    }
}
