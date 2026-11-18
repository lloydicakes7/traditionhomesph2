@props(['model'])

<x-adminlte-card body-class="py-2">
    <div class="d-flex flex-wrap">
        <x-adminlte-button label="Download Template" theme="info" icon="fas fa-fw fa-download"
            wire:click="downloadTemplate" wire:loading.attr="disabled"
            data-thha-progress="Preparing the CSV template..." class="btn-sm mr-1 mb-1" />

        @can('import', $model)
            <x-adminlte-button label="Import CSV" theme="success" icon="fas fa-fw fa-file-import"
                wire:click="openImportModal" wire:loading.attr="disabled" class="btn-sm mr-1 mb-1" />
        @endcan

        @can('export', $model)
            <x-adminlte-button label="Export CSV" theme="primary" icon="fas fa-fw fa-file-export"
                wire:click="export" wire:loading.attr="disabled"
                data-thha-progress="Exporting the CSV file..." class="btn-sm mb-1" />
        @endcan
    </div>
</x-adminlte-card>
