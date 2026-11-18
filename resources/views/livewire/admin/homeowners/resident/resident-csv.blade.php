<div>
    <x-ui.modals.import-export-card :model="\App\Models\Resident::class" />

    @livewire('admin.modals.csv-import-modal')
    @livewire('admin.modals.csv-import-result-modal')
</div>
