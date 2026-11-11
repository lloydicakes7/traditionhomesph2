<div class="d-flex justify-content-between align-items-center mb-2">
    <label class="mb-0">Account Lines</label>
</div>

<div class="table-responsive border rounded">
    <table class="table table-sm mb-0">
        <thead class="bg-light">
            <tr>
                <th scope="col" style="min-width:260px;">Account</th>
                <th scope="col" style="min-width:120px;">From / To</th>
                <th scope="col" style="min-width:140px;">Amount</th>
                <th scope="col" style="min-width:50px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lines as $index => $line)
                <tr wire:key="journal-line-{{ $index }}">
                    <td>
                        <select id="journalLineAccount{{ $index }}"
                            class="form-control @error('lines.' . $index . '.account_id') is-invalid @enderror"
                            data-thha-select2 wire:model="lines.{{ $index }}.account_id"
                            aria-label="Account for line {{ $index + 1 }}">
                            <option value="">Choose an account…</option>
                            @foreach ($manualAccountOptions as $id => $label)
                                <option value="{{ $id }}" {{ (string) $line['account_id'] === (string) $id ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error("lines.{$index}.account_id")
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </td>
                    <td>
                        <select class="form-control @error('lines.' . $index . '.side') is-invalid @enderror"
                            wire:model="lines.{{ $index }}.side"
                            aria-label="Direction for line {{ $index + 1 }}">
                            <option value="debit">To</option>
                            <option value="credit">From</option>
                        </select>
                        @error("lines.{$index}.side")
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01" placeholder="0.00"
                            class="form-control @error('lines.' . $index . '.amount') is-invalid @enderror"
                            wire:model.lazy="lines.{{ $index }}.amount"
                            aria-label="Amount for line {{ $index + 1 }}">
                        @error("lines.{$index}.amount")
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </td>
                    <td class="text-right">
                        <button type="button" class="btn btn-danger btn-sm"
                            title="Remove line" aria-label="Remove line {{ $index + 1 }}"
                            wire:click.prevent="removeLine({{ $index }})">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@error('lines')
    <span class="text-danger small d-block mt-1">{{ $message }}</span>
@enderror

<button type="button" class="btn btn-primary btn-sm mt-2" wire:click="addLine"
    wire:loading.attr="disabled">
    <i class="fas fa-fw fa-plus"></i> Add Line
</button>

<div class="row justify-content-end mt-3">
    <div class="col-12 col-sm-5">
        <dl class="row mb-0 small">
            <dt class="col-7 text-muted font-weight-normal">Total To</dt>
            <dd class="col-5 text-right">{{ \App\Utility\MoneyHelper::format($totals['to']) }}</dd>

            <dt class="col-7 text-muted font-weight-normal">Total From</dt>
            <dd class="col-5 text-right">{{ \App\Utility\MoneyHelper::format($totals['from']) }}</dd>

            <dt class="col-7 font-weight-bold border-top pt-2">Entry</dt>
            <dd class="col-5 text-right border-top pt-2">
                @if ($balanced)
                    <span class="text-success"><i class="fas fa-fw fa-check-circle"></i> Balanced</span>
                @else
                    <span class="text-warning"><i class="fas fa-fw fa-exclamation-triangle"></i> Not balanced</span>
                @endif
            </dd>
        </dl>
    </div>
</div>
