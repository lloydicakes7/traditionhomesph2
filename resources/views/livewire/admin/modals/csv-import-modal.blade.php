<div wire:ignore.self>
    <x-adminlte-modal id="csvImportModal"
        title="{{ $title }}" icon="fas fa-fw fa-file-import" theme="success"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading import options…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="text-muted small mb-3">
                @if (filled($columns))
                    Upload a CSV file with the following columns:
                    <br>
                    <strong>{{ $columns }}</strong>.
                    <br>
                @endif
                Download the template first to see the required format.
            </p>

            <div wire:ignore>
                <x-adminlte-input-file-krajee id="csvImportFile" name="importFile" label="Choose CSV file..."
                    wire:model="importFile" igroup-size="sm" :disable-feedback="true"
                    :config="[
                        'allowedFileExtensions' => ['csv', 'txt'],
                        'maxFileCount' => 1,
                        'showUpload' => false,
                        'showCancel' => false,
                        'msgPlaceholder' => 'file.csv',
                    ]" />
            </div>

            <div wire:loading wire:target="importFile" class="mt-1">
                <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Import" theme="success" icon="fas fa-fw fa-file-import"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm" />
            <span wire:loading wire:target="confirm" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Importing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>

@push('js')
<script>
    $(function () {
        var $fileInput = $('#csvImportFile');

        $fileInput.on('filebatchselected.csvImport', function (event, files) {
            if (! files || files.length === 0) {
                return;
            }

            var transfer = new DataTransfer();
            transfer.items.add(files[0]);
            this.files = transfer.files;
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        $fileInput.on('fileclear.csvImport', function () {
            if (! clearingWidget) {
                @this.set('importFile', null);
            }
        });

        var clearingWidget = false;

        var clearWidget = function () {
            if (! $fileInput.data('fileinput')) {
                return;
            }

            clearingWidget = true;
            $fileInput.fileinput('clear');
            clearingWidget = false;
        };

        // The shell itself is Bootstrap's own (modal-loader.js handles
        // `modal-open` / `modal-close`); this only resets the Krajee widget so a
        // closed dialog never carries a stale file into the next import.
        window.addEventListener('modal-open', function (event) {
            var detail = event.detail || {};
            var id = typeof detail === 'string' ? detail : detail.id;

            if (id === 'csvImportModal') {
                clearWidget();
            }
        });

        window.addEventListener('modal-close', function (event) {
            var detail = event.detail || {};
            var id = typeof detail === 'string' ? detail : detail.id;

            if (id === 'csvImportModal') {
                clearWidget();
            }
        });
    });
</script>
@endpush
