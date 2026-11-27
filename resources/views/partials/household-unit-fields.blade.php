@php
    $unitNamed = trim((string) ($property['block'] ?? '')) !== '' && trim((string) ($property['lot'] ?? '')) !== '';
    $occupied = $unit !== null && $unit['is_occupied'];
    $ownHead = $ownHead ?? false;
    $canJoin = ($canJoin ?? true) && ! $ownHead;
    $canChangeTenure = $canChangeTenure ?? ! $occupied;
@endphp

<div class="form-group">
    <label for="householdUnitAddress">{{ __('households.association_address') }}</label>
    <p id="householdUnitAddress" class="form-control-plaintext border rounded bg-light px-2 py-1 mb-0 small">
        <i class="fas fa-fw fa-map-marker-alt mr-1"></i>
        {{ $address ? $address->full_address : 'No Address was created by the office.' }}
    </p>
</div>

<div class="row">
    <div class="col-6">
        <x-adminlte-input name="property.block" type="text" maxlength="20" inputmode="numeric"
            label="{{ __('households.block') }} *"
            wire:model.lazy="property.block" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-th"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-6">
        <x-adminlte-input name="property.lot" type="text" maxlength="20" data-thha-uppercase
            label="{{ __('households.lot') }} *"
            wire:model.lazy="property.lot" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-th-large"></i></div></x-slot>
        </x-adminlte-input>
    </div>

    @if ($unitNamed && $unit === null)
        <div class="col-12 col-md-6">
            <x-adminlte-input name="property.sqm" type="number" step="0.01" min="0" max="99999"
                label="{{ __('households.floor_area') }}" placeholder="e.g., 100"
                wire:model.defer="property.sqm" igroup-size="sm">
                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ruler-combined"></i></div></x-slot>
            </x-adminlte-input>
        </div>
        <div class="col-12 col-md-6">
            <x-adminlte-select class="text-uppercase" name="property.type" label="{{ __('households.unit_type') }} *"
                wire:model.defer="property.type" igroup-size="sm">
                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-home"></i></div></x-slot>
                <option value="">Select</option>
                @foreach ($propertyTypeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select>
        </div>
    @endif

    @if ($unitNamed && $canJoin)
        <div class="col-12 col-md-6">
            <x-adminlte-input name="household.relationship"
                label="{{ __('households.relationship') }}" maxlength="255" data-thha-uppercase
                placeholder="{{ __('households.relationship_hint') }}"
                wire:model.defer="household.relationship" igroup-size="sm">
                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-users"></i></div></x-slot>
            </x-adminlte-input>
        </div>
    @endif

    @if ($unitNamed && $canChangeTenure)
        <div class="col-12 col-md-6">
            <x-adminlte-select class="text-uppercase" name="household.type" label="{{ __('households.tenure') }} *"
                wire:model.defer="household.type" igroup-size="sm">
                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-key"></i></div></x-slot>
                @foreach ($householdTypeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select>
        </div>
        <div class="col-12">
            <x-adminlte-textarea name="household.prev_address" label="{{ __('households.previous_address') }}"
                rows="2" maxlength="500" placeholder="Optional" data-thha-uppercase
                wire:model.defer="household.prev_address" igroup-size="sm">
                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-signs"></i></div></x-slot>
            </x-adminlte-textarea>
        </div>
    @endif
</div>

<div class="alert alert-info small py-2 mt-4 mb-0">
    <i class="fas fa-fw fa-info-circle mr-1"></i>{{ $note }}

    @if ($unitNamed && $occupied && $canJoin)
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="householdBeHead"
                wire:model.defer="household.be_head">
            <label class="custom-control-label" for="householdBeHead">
                {{ __('households.be_head_toggle') }}
                <small class="d-block text-muted">{{ __('households.be_head_hint') }}</small>
            </label>
        </div>
    @endif
</div>
