<div>
    <x-ui.modals.import-export-card :model="\App\Models\Property::class" />

    @livewire('admin.modals.csv-import-modal')
    @livewire('admin.modals.csv-import-result-modal')
</div>
