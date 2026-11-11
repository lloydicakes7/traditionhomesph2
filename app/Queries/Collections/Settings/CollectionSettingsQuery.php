<?php

namespace App\Queries\Collections\Settings;

use App\Enums\AdjustmentType;
use App\Enums\BillingMethod;
use App\Enums\GenerationMode;
use App\Models\Property;
use App\Utility\HtmlHelper;
use App\Utility\MoneyHelper;
use App\Utility\SettingsHelper;
use Illuminate\Support\Carbon;

/**
 * The association's collections configuration, as every screen reads it.
 *
 * The office keeps its billing method, its rate, its penalty and its invoice
 * instructions in the settings table; this is the one place that turns those
 * stored strings into the values the module works with, so the settings form,
 * the dues preview and the statement a homeowner receives cannot disagree.
 */
class CollectionSettingsQuery
{
    const CACHE_KEY = 'settings.collections';

    const GROUP = 'collections';

    const DEFAULTS = [
        'billing_method'       => BillingMethod::PER_SQM,
        'monthly_rate'         => 0,
        'generation_mode'      => GenerationMode::MANUAL,
        'generation_day'       => 1,
        'grace_period_days'    => 0,
        'auto_email_reminders' => 0,
        'penalty_type'         => AdjustmentType::FIXED,
        'penalty_value'        => 0,
        'invoice_instructions' => '',
    ];

    // ===== Read =====

    public function get(string $key, $default = null)
    {
        return $this->load()[$key] ?? $default;
    }

    public function billingMethod(): BillingMethod
    {
        return BillingMethod::tryFrom($this->get('billing_method')) ?: BillingMethod::PER_SQM();
    }

    public function generationMode(): GenerationMode
    {
        return GenerationMode::tryFrom($this->get('generation_mode')) ?: GenerationMode::MANUAL();
    }

    public function penaltyType(): AdjustmentType
    {
        return AdjustmentType::tryFrom($this->get('penalty_type')) ?: AdjustmentType::FIXED();
    }

    public function rate(): float
    {
        return (float) $this->get('monthly_rate', 0);
    }

    public function dueFor(Property $property): float
    {
        $rate = $this->rate();

        if ($this->billingMethod()->isPerSquareMeter()) {
            return round($rate * (float) $property->sqm, 2);
        }

        return round($rate, 2);
    }

    public function penaltyFor(float $amount): float
    {
        return $this->penaltyType()->compute($amount, (float) $this->get('penalty_value'));
    }

    public function dueDateFor(string $month): Carbon
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $day = min(max(1, (int) $this->get('generation_day', 1)), $start->daysInMonth);
        $grace = (int) $this->get('grace_period_days', 0);

        $scheduled = $start->copy()->day($day)->addDays($grace);
        $fromToday = Carbon::now()->startOfDay()->addDays($grace);

        return $scheduled->greaterThan($fromToday) ? $scheduled : $fromToday;
    }

    // ===== Queries =====

    public function form(): array
    {
        return $this->load();
    }

    public function placeholders(): array
    {
        return [
            '{{OWNER_NAME}}',
            '{{PROPERTY}}',
            '{{AMOUNT_DUE}}',
            '{{DUE_DATE}}',
            '{{HOA_NAME}}',
            '{{HOA_PORTAL_LINK}}',
        ];
    }

    public function renderInstructions(array $values = []): string
    {
        $instructions = (string) $this->get('invoice_instructions', '');

        foreach ($values as $key => $value) {
            $instructions = str_replace('{{' . $key . '}}', $value, $instructions);
        }

        foreach ($this->placeholders() as $placeholder) {
            $instructions = str_replace($placeholder, '', $instructions);
        }

        // Token retired with the online-payment block: old saved text may still
        // carry it, and a raw token must never reach a homeowner.
        $instructions = str_replace('{{PAYMENT_METHODS_TABLE}}', '', $instructions);

        $instructions = trim($instructions);

        return HtmlHelper::isPlainText($instructions)
            ? HtmlHelper::toParagraphs($instructions)
            : $instructions;
    }

    public function previewInstructions(array $values = []): string
    {
        $filled = array_filter($values, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->renderInstructions(array_merge($this->exampleValues(), $filled));
    }

    // ===== Helpers =====

    private function load(): array
    {
        $config = array_merge(self::DEFAULTS, SettingsHelper::getByGroup(self::GROUP));

        if (! HtmlHelper::isPlainText((string) $config['invoice_instructions'])) {
            $config['invoice_instructions'] = HtmlHelper::sanitize((string) $config['invoice_instructions']);
        }

        $config['generation_day'] = (int) $config['generation_day'];
        $config['grace_period_days'] = (int) $config['grace_period_days'];
        $config['penalty_value'] = (float) $config['penalty_value'];
        $config['auto_email_reminders'] = (bool) ((int) $config['auto_email_reminders']);

        return $config;
    }

    private function exampleValues(): array
    {
        return [
            'OWNER_NAME'            => 'Juan Dela Cruz',
            'PROPERTY'              => 'Block 4 Lot 12',
            'AMOUNT_DUE'            => MoneyHelper::format(1500),
            'DUE_DATE'              => Carbon::now()->addDays(7)->format('M j, Y'),
            'HOA_NAME'              => config('thha.hoa_name'),
            'HOA_PORTAL_LINK'       => url('/'),
        ];
    }
}
