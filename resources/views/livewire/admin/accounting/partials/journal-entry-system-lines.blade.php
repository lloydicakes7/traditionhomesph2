<div class="table-responsive">
    <table class="table table-sm mb-0">
        <thead>
            <tr>
                <th style="width: 56%;">Account</th>
                <th class="text-right" style="width: 22%;">To</th>
                <th class="text-right" style="width: 22%;">From</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lines as $line)
                <tr>
                    <td><x-ui.value :value="$line['account_name'] ?? null" /></td>
                    <td class="text-right">
                        {{ $line['side'] === 'debit' ? \App\Utility\MoneyHelper::format($line['amount']) : '—' }}
                    </td>
                    <td class="text-right">
                        {{ $line['side'] === 'credit' ? \App\Utility\MoneyHelper::format($line['amount']) : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
