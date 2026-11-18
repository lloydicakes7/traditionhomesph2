<div>
    <x-adminlte-modal id="csvImportResultModal" title="{{ $title }}" icon="fas fa-fw fa-clipboard-check"
        theme="{{ $this->outcome() }}" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the import results..." />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="alert alert-{{ $this->outcome() }} mb-3">
                <strong>{{ $this->summary() }}</strong>
            </div>

            @if ($error)
                <h6 class="font-weight-bold">What went wrong</h6>
                <p class="mb-3">{{ $error }}</p>

                <h6 class="font-weight-bold">How to fix it</h6>
                <p class="mb-0">
                    Download the template, keep its first row unchanged, then fill in the rows below it
                    and import the file again.
                </p>
            @elseif ($skipped !== [])
                <h6 class="font-weight-bold">How to fix it</h6>
                <p>
                    Correct the rows above in the spreadsheet, then import the file again. The row number
                    counts the header as row 1.
                </p>

                <h6 class="font-weight-bold">Rows that were skipped</h6>
                <ul class="small mb-3 pl-3">
                    @foreach ($skipped as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mb-0">Every row in the file was saved.</p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Done" theme="secondary" class="btn-sm" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>
</div>
