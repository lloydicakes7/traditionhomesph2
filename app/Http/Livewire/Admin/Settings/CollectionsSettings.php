<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Collections\Settings\UpdateCollectionSettings;
use App\Enums\AdjustmentType;
use App\Enums\BillingMethod;
use App\Enums\GenerationMode;
use App\Http\Livewire\AdminComponent;
use App\Models\Setting;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use Illuminate\Validation\Rule;

class CollectionsSettings extends AdminComponent
{
    // ------------------------------------------------------------------
    // Tabs
    // ------------------------------------------------------------------

    public $activeTab = 'billing';

    const TABS = ['billing', 'payments'];

    // ------------------------------------------------------------------
    // Monthly Dues & Billing
    // ------------------------------------------------------------------

    public $billing_method = '';

    public $monthly_rate = 0;

    public $generation_mode = '';

    public $generation_day = 1;

    public $grace_period_days = 0;

    public $auto_email_reminders = false;

    public $penalty_type = '';

    public $penalty_value = 0;

    // ------------------------------------------------------------------
    // Payments & Receipts
    // ------------------------------------------------------------------

    public $invoice_instructions = '';

    protected $listeners = [
        'payment-method-saved'   => '$refresh',
        'payment-method-deleted' => '$refresh',
    ];

    /** @var PaymentMethodQuery */
    protected $paymentMethodQuery;

    /** @var CollectionSettingsQuery */
    protected $settingsQuery;

    /** @var UpdateCollectionSettings */
    protected $updateSettings;

    public function boot(
        PaymentMethodQuery $paymentMethodQuery,
        CollectionSettingsQuery $settingsQuery,
        UpdateCollectionSettings $updateSettings
    ) {
        $this->paymentMethodQuery = $paymentMethodQuery;
        $this->settingsQuery = $settingsQuery;
        $this->updateSettings = $updateSettings;
    }

    public function mount(): void
    {
        $settings = $this->settingsQuery->form();

        $this->billing_method = (string) $settings['billing_method'];
        $this->generation_mode = (string) $settings['generation_mode'];
        $this->generation_day = (int) $settings['generation_day'];
        $this->grace_period_days = (int) $settings['grace_period_days'];
        $this->auto_email_reminders = (bool) $settings['auto_email_reminders'];
        $this->penalty_type = (string) $settings['penalty_type'];
        $this->penalty_value = (float) $settings['penalty_value'];
        $this->monthly_rate = (float) $settings['monthly_rate'];
        $this->invoice_instructions = (string) $settings['invoice_instructions'];
    }

    // =====================================================================
    // Monthly Dues & Billing
    // =====================================================================

    /**
     * Save how dues are computed and when they are raised.
     */
    public function saveBilling(): void
    {
        $this->authorize('update', Setting::class);

        $this->validate([
            'billing_method'        => ['required', Rule::in(BillingMethod::values())],
            'monthly_rate'          => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'generation_mode'       => ['required', Rule::in(GenerationMode::values())],
            'generation_day'        => ['required', 'integer', 'min:1', 'max:28'],
            'grace_period_days'     => ['required', 'integer', 'min:0', 'max:60'],
            'penalty_type'          => ['required', Rule::in(AdjustmentType::values())],
            'penalty_value'         => ['required', 'numeric', 'min:0'],
            'auto_email_reminders'  => ['boolean'],
        ], [
            'generation_day.min'   => 'The generation day must fall between the 1st and the 28th, so every month has it.',
            'generation_day.max'   => 'The generation day must fall between the 1st and the 28th, so every month has it.',
            'grace_period_days.max' => 'A grace period of more than 60 days is almost certainly a mistake.',
            'penalty_value.min'    => 'The penalty cannot be negative.',
            'monthly_rate.required' => 'Set the monthly rate.',
            'monthly_rate.min'      => 'The monthly rate cannot be negative.',
            'monthly_rate.regex'    => 'The monthly rate may carry at most two decimals.',
        ], [
            'billing_method'       => 'billing method',
            'generation_mode'      => 'generation mode',
            'generation_day'       => 'generation day',
            'grace_period_days'    => 'grace period',
            'penalty_type'         => 'penalty type',
            'penalty_value'        => 'penalty',
            'monthly_rate'         => 'monthly rate',
        ]);

        $payload = [
            'billing_method'       => $this->billing_method,
            'monthly_rate'         => $this->monthly_rate,
            'generation_mode'      => $this->generation_mode,
            'generation_day'       => $this->generation_day,
            'grace_period_days'    => $this->grace_period_days,
            'penalty_type'         => $this->penalty_type,
            'penalty_value'        => $this->penalty_value,
            'auto_email_reminders' => $this->auto_email_reminders,
        ];

        $this->updateSettings->handle($payload, 'Updated the monthly dues and billing settings', auth('web')->user());

        $this->emit('notify', 'success', 'Billing settings saved.');
    }

    // =====================================================================
    // Payments & Receipts
    // =====================================================================

    /**
     * Save the instructions that go on every invoice.
     *
     * The editor hands over HTML; the service sanitizes and stores it (see
     * \App\Utility\HtmlHelper), so nothing is cleaned here.
     */
    public function saveInstructions(): void
    {
        $this->authorize('update', Setting::class);

        // The instructions are rich text: the limit is generous because the
        // editor's own markup counts towards it.
        $this->validate([
            'invoice_instructions' => ['nullable', 'string', 'max:20000'],
        ], [], [
            'invoice_instructions' => 'invoice instructions',
        ]);

        $this->updateSettings->handle(
            ['invoice_instructions' => $this->invoice_instructions],
            'Updated the invoice and receipt instructions',
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Invoice instructions saved.');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Setting::class);

        return view('livewire.admin.settings.collections-settings', [
            'billingMethods'   => BillingMethod::cases(),
            'generationModes'  => GenerationMode::cases(),
            'penaltyTypes'     => AdjustmentType::labels(),
            'paymentMethods'   => $this->paymentMethodQuery->paginate(),
            'placeholders'     => $this->settingsQuery->placeholders(),
            'instructionsPreview' => $this->settingsQuery->previewInstructions([
                'HOA_NAME' => config('app.name'),
            ]),
        ]);
    }
}
