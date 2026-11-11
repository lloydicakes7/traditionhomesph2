{{-- Import/export buttons header card (shared by Homeowners module pages) --}}
<x-adminlte-card>
    <div class="row justify-content-end">
        <div class="col-12 col-sm-auto mb-1 mb-sm-0">
            <x-adminlte-button label="Download Template" theme="info" icon="fas fa-fw fa-download"
                wire:click="downloadTemplate" wire:loading.attr="disabled"
                data-thha-progress="Preparing the CSV template…" class="btn-sm btn-block" />
        </div>
        @can('import', $model)
            <div class="col-12 col-sm-auto mb-1 mb-sm-0">
                <x-adminlte-button label="Import CSV" theme="success" icon="fas fa-fw fa-file-import"
                    wire:click="openImportModal" wire:loading.attr="disabled" class="btn-sm btn-block" />
            </div>
        @endcan
        @can('export', $model)
            <div class="col-12 col-sm-auto">
                <x-adminlte-button label="Export CSV" theme="primary" icon="fas fa-fw fa-file-export"
                    wire:click="export" wire:loading.attr="disabled"
                    data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
            </div>
        @endcan
    </div>
</x-adminlte-card>
