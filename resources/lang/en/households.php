<?php

return [

    'association_address' => 'Association address',
    'block'               => 'Block',
    'lot'                 => 'Lot',
    'floor_area'          => 'Floor area (sqm)',
    'unit_type'           => 'Unit type',
    'tenure'              => 'Tenure',
    'previous_address'    => 'Previous address',
    'relationship'        => 'Relationship',
    'relationship_hint'   => 'e.g., Spouse, Son',
    'be_head_toggle'      => 'Toggle to take over as the household head',
    'be_head_hint'        => 'The household head on file confirms this request before the office can approve your profile.',

    'link' => [
        'title'          => 'Household Requests',
        'heading'        => 'Requests to join your household',
        'hint'           => 'Confirm only the people who belong to your household.',
        'resident'       => 'Resident',
        'relationship'   => 'Relationship',
        'requested'      => 'Requested',
        'answer'         => 'Answer',
        'approve'        => 'Approve',
        'reject'         => 'Reject',
        'reject_confirm' => 'Reject this resident? They will not be added to your household.',
        'approved'       => 'The resident is now a member of your household.',
        'rejected'       => 'The request was rejected. The resident is not part of your household.',
        'head_request'   => 'Wants to be the head',
        'empty'          => 'No resident is waiting for your answer.',
        'failed'         => 'That request can no longer be answered.',
    ],

    'note' => [
        'name_unit'     => 'Enter the block and lot so the association can find the unit.',
        'new_unit'      => 'No unit is on file at this block and lot yet. It will be recorded with the floor area and type above.',
        'vacant'        => 'No household is registered at this unit yet. The person placed here is recorded as its household head.',
        'occupied'      => 'This unit already has a household. The household head on file confirms whether the person placed here joins it, and as whom.',
        'own_head'      => 'You are the household head of this property. Your household and its members are already on file.',
        'own_household' => 'This is the address on file for this household. Change the block and lot to move it to another unit.',
        'occupied_move' => 'Another household already lives at this address. The household on file moves with this homeowner, so choose a free unit.',
        'not_placed'    => 'No unit is on file for this homeowner yet. Name the block and lot to place the household.',
        'no_association_address' => 'The association address is not recorded yet, so a new unit cannot be filed.',
    ],

];
