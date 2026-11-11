@props([
    'placeholder' => 'Search...',
    'apply'       => 'applySearch',
])

<x-adminlte-card>
    <form wire:submit.prevent="{{ $apply }}" role="search">
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <div class="flex-grow-1 mr-2">
                    <x-adminlte-input name="search" placeholder="{{ $placeholder }}"
                        wire:model.defer="search" igroup-size="sm" fgroup-class="mb-0">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                <x-adminlte-button type="submit" label="Search" theme="primary" icon="fas fa-fw fa-search"
                    class="btn-sm" wire:loading.attr="disabled" />
            </div>
        </div>
    </form>
</x-adminlte-card>
