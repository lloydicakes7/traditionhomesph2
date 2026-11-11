<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Account\Account;
use App\Models\JournalEntry\JournalLine;
use Illuminate\Database\Eloquent\Builder;

trait EditsJournalLines
{
    /** @var array<int, array<string, string>> The entry's lines */
    public $lines = [];

    // =====================================================================
    // Setup
    // =====================================================================

    /**
     * @return array<string, string>
     */
    protected function lineRules(): array
    {
        return [
            'lines'              => 'required|array|min:2',
            'lines.*.account_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! is_numeric($value)
                        || ! Account::query()->notSystem()->whereKey((int) $value)->exists()) {
                        $fail('Choose an account from the chart.');
                    }
                },
            ],
            'lines.*.side'       => 'required|in:' . JournalLine::SIDE_DEBIT . ',' . JournalLine::SIDE_CREDIT,
            'lines.*.amount'     => 'required|numeric|min:0.01|max:9999999999.99',
        ];
    }

    // =====================================================================
    // Actions
    // =====================================================================

    public function addLine(): void
    {
        $last = end($this->lines);
        $side = is_array($last) && isset($last['side']) ? $last['side'] : JournalLine::SIDE_DEBIT;

        $this->lines[] = $this->blankLine($side);
    }

    /**
     * @param  int|string  $index
     */
    public function removeLine($index): void
    {
        unset($this->lines[(int) $index]);

        $this->lines = array_values($this->lines);

        while (count($this->lines) < 2) {
            $this->lines[] = $this->blankLine();
        }
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * @return array<string, string>
     */
    protected function blankLine(string $side = JournalLine::SIDE_DEBIT): array
    {
        return [
            'account_id' => '',
            'side'       => $side,
            'amount'     => '',
        ];
    }

    protected function normalizeLines(): void
    {
        $this->lines = array_values(array_filter($this->lines, function (array $line) {
            return trim((string) ($line['account_id'] ?? '')) !== ''
                || trim((string) ($line['amount'] ?? '')) !== '';
        }));
    }

    /**
     * @return array{to: string, from: string, difference: string}
     */
    protected function totals(): array
    {
        $to = '0.00';
        $from = '0.00';

        foreach ($this->lines as $line) {
            $amount = $this->lineAmount($line['amount'] ?? null);

            if (($line['side'] ?? '') === JournalLine::SIDE_CREDIT) {
                $from = bcadd($from, $amount, 2);
            } else {
                $to = bcadd($to, $amount, 2);
            }
        }

        return [
            'to'         => $to,
            'from'       => $from,
            'difference' => bcsub($to, $from, 2),
        ];
    }

    protected function isBalanced(): bool
    {
        $totals = $this->totals();

        return bccomp($totals['to'], '0', 2) > 0
            && bccomp($totals['to'], $totals['from'], 2) === 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function journalLines(): array
    {
        return array_map(function (array $line) {
            return [
                'account_id' => (int) $line['account_id'],
                'side'       => $line['side'] === JournalLine::SIDE_CREDIT
                    ? JournalLine::SIDE_CREDIT
                    : JournalLine::SIDE_DEBIT,
                'amount'     => $this->lineAmount($line['amount']),
            ];
        }, $this->lines);
    }

    /**
     * @param  mixed  $amount
     */
    private function lineAmount($amount): string
    {
        return is_numeric($amount) ? bcadd((string) $amount, '0', 2) : '0.00';
    }

    /**
     * @return array<int, string>
     */
    protected function accountOptions(): array
    {
        return $this->optionsFrom(Account::query());
    }

    /**
     * @return array<int, string>
     */
    protected function manualAccountOptions(): array
    {
        return $this->optionsFrom(Account::query()->notSystem());
    }

    /**
     * @return array<int, string>
     */
    private function optionsFrom(Builder $query): array
    {
        return $query
            ->ordered()
            ->get()
            ->mapWithKeys(function (Account $account) {
                return [$account->id => $account->label];
            })
            ->all();
    }
}
