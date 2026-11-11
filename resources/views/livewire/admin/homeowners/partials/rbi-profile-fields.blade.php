<div>
    <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
        <i class="fas fa-fw fa-tags mr-1"></i>Sectoral Tags
    </h6>

    <div class="row">
        @foreach ($sectorOptions as $value => $label)
            <div class="col-md-4 mb-1">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="{{ $prefix }}-sector-{{ $value }}"
                        value="{{ $value }}" wire:model.defer="{{ $prefix }}.sectors">
                    <label class="custom-control-label" for="{{ $prefix }}-sector-{{ $value }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
</div>
