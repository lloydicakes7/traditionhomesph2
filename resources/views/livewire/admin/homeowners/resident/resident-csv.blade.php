<div>
    {{-- =================================================================== --}}
    {{-- CSV TOOLBAR — export / template / import (WithCsvImport hooks)      --}}
    {{-- Own component so importing never re-renders the table.              --}}
    {{-- =================================================================== --}}
    <x-ui.modals.import-export-card :model="\App\Models\Resident::class" />

    {{-- Shared import modal (this component handles csv-import-confirmed) --}}
    @livewire('admin.modals.csv-import-modal')
</div>
