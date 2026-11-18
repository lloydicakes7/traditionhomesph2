<div>
    <x-adminlte-modal id="csvImportModal" title="{{ $title }}" icon="fas fa-fw fa-file-csv"
        theme="success" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading import options..." />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">

            @if (filled($columns))
                <p class="small text-muted mb-2">The first row must have these columns:</p>

                <div class="mb-3">
                    @foreach ($columns as $column)
                        <span class="badge badge-light border text-monospace mr-1 mb-1">{{ $column }}</span>
                    @endforeach
                </div>
            @endif

            <label data-csv-drop class="d-block border rounded bg-light text-center px-3 py-4 mb-0">
                <i class="fas fa-fw fa-cloud-upload-alt fa-2x text-success mb-2"></i>
                <span class="d-block font-weight-bold">Drop the CSV file here</span>
                <span class="d-block small text-muted">or click to choose a file. CSV only, up to 2 MB.</span>
                <input type="file" id="csvImportFile" class="sr-only"
                    accept=".csv,.txt,text/csv" wire:model="importFile">
            </label>

            @if ($importFile)
                <div class="small mt-2">
                    <i class="fas fa-fw fa-file-csv text-success mr-1"></i>
                    {{ $importFile->getClientOriginalName() }}
                    <span class="text-muted">{{ number_format($importFile->getSize() / 1024, 0) }} KB</span>
                </div>
            @endif

            <div data-csv-progress class="progress mt-2 d-none" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: 0;"></div>
            </div>

            <div wire:loading wire:target="importFile" class="small text-info mt-2">
                <i class="fas fa-spinner fa-spin mr-1"></i> Uploading the file...
            </div>

            @error('importFile')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" class="btn-sm" data-dismiss="modal" />
            <x-adminlte-button label="Import" theme="success" icon="fas fa-fw fa-file-import"
                class="btn-sm" wire:click="confirm" wire:loading.attr="disabled"
                data-thha-progress="Importing the CSV file..." data-thha-progress-wait />
        </x-slot>
    </x-adminlte-modal>
</div>

@push('js')
<script>
    (function () {
        function bind() {
            var modal = document.getElementById('csvImportModal');

            if (!modal || modal.getAttribute('data-csv-bound') === '1') {
                return;
            }

            modal.setAttribute('data-csv-bound', '1');

            function zone() {
                return modal.querySelector('[data-csv-drop]');
            }

            function input() {
                return modal.querySelector('#csvImportFile');
            }

            function highlight(on) {
                var drop = zone();

                if (drop) {
                    drop.classList.toggle('bg-light', ! on);
                    drop.classList.toggle('bg-white', on);
                }
            }

            function accept(files) {
                var field = input();

                if (!field || !files || files.length === 0) {
                    return;
                }

                var transfer = new DataTransfer();
                transfer.items.add(files[0]);
                field.files = transfer.files;
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }

            modal.addEventListener('dragover', function (event) {
                event.preventDefault();
                highlight(true);
            });

            modal.addEventListener('dragleave', function (event) {
                if (!modal.contains(event.relatedTarget)) {
                    highlight(false);
                }
            });

            modal.addEventListener('drop', function (event) {
                event.preventDefault();
                highlight(false);
                accept(event.dataTransfer ? event.dataTransfer.files : null);
            });

            window.addEventListener('livewire-upload-progress', function (event) {
                var bar = modal.querySelector('[data-csv-progress]');

                if (!bar || event.detail.id !== 'csvImportFile') {
                    return;
                }

                bar.classList.remove('d-none');
                bar.querySelector('.progress-bar').style.width = event.detail.progress + '%';
            });

            window.addEventListener('livewire-upload-finish', function () {
                resetProgress();
            });

            window.addEventListener('livewire-upload-error', function () {
                resetProgress();
            });


            function resetProgress() {
                var bar = modal.querySelector('[data-csv-progress]');

                if (!bar) {
                    return;
                }

                bar.classList.add('d-none');
                bar.querySelector('.progress-bar').style.width = '0%';
            }
        }

        if (window.Livewire) {
            bind();
        }

        document.addEventListener('livewire:load', bind);
        document.addEventListener('DOMContentLoaded', bind);
    })();
</script>
@endpush
