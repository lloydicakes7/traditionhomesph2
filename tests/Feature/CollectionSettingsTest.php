<?php

namespace Tests\Feature;

use App\Actions\Collections\Settings\UpdateCollectionSettings;
use App\Enums\BillingMethod;
use App\Http\Livewire\Admin\Settings\CollectionsSettings;
use App\Http\Livewire\Admin\Settings\Modals\PaymentMethodFormModal;
use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Two things the Collections settings page hands to the system rather than to
 * the officer: where a payment method sits in the list, and what the rich-text
 * Bill & Receipt Instructions are allowed to carry.
 */
class CollectionSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function save(array $data): void
    {
        app(UpdateCollectionSettings::class)->handle($data, 'Updated the collections settings', $this->settingsOfficer());
    }

    private function settingsOfficer(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'settings-fixture-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    /**
     * An officer with the panel's full access — the settings pages are
     * Administrator-only.
     */
    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'collections-settings-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    // =====================================================================
    // Payment methods: the order is the system's
    // =====================================================================

    public function test_a_new_payment_method_is_placed_last_by_the_system(): void
    {
        $this->signIn();

        PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 1]);

        Livewire::test(PaymentMethodFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'GCash')
            ->set('form.details', '0945 351 3307')
            ->call('confirm')
            ->assertHasNoErrors();

        $gcash = PaymentMethod::query()->where('name', 'GCash')->first();

        $this->assertSame(2, $gcash->sort_order);

        // ...and one added after it, after that.
        Livewire::test(PaymentMethodFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'Bank Transfer')
            ->call('confirm')
            ->assertHasNoErrors();

        $this->assertSame(3, PaymentMethod::query()->where('name', 'Bank Transfer')->first()->sort_order);
    }

    public function test_editing_a_payment_method_leaves_it_where_it_is(): void
    {
        $this->signIn();

        $cash = PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 1]);
        PaymentMethod::create(['name' => 'GCash', 'is_active' => true, 'sort_order' => 2]);

        Livewire::test(PaymentMethodFormModal::class)
            ->call('openEdit', $cash->id)
            ->set('form.name', 'Cash on hand')
            ->call('confirm')
            ->assertHasNoErrors();

        $this->assertSame(1, $cash->refresh()->sort_order);
    }

    public function test_the_payment_method_dialog_asks_for_no_display_order(): void
    {
        $this->signIn();

        Livewire::test(PaymentMethodFormModal::class)
            ->call('openCreate')
            ->assertDontSee('Display Order');
    }

    // =====================================================================
    // Monthly dues: one rate for every property type
    // =====================================================================

    public function test_one_monthly_rate_is_saved_for_every_property_type(): void
    {
        $this->signIn();

        Livewire::test(CollectionsSettings::class)
            ->set('billing_method', BillingMethod::FIXED_RATE)
            ->set('monthly_rate', '1500.50')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $settings = SettingsHelper::getByGroup('collections');

        $this->assertSame('1500.50', $settings['monthly_rate']);
        $this->assertArrayNotHasKey('rate_house', $settings);
        $this->assertArrayNotHasKey('rate_townhouse', $settings);
    }

    // =====================================================================
    // Email Construction: rich text, sanitized
    // =====================================================================

    public function test_the_instructions_are_stored_as_sanitized_html(): void
    {
        $this->save([
            'invoice_instructions' => '<p style="text-align: center">Hi {{OWNER_NAME}}</p>'
                . '<script>alert(1)</script><iframe src="https://example.test"></iframe>',
        ]);

        $stored = app(CollectionSettingsQuery::class)->get('invoice_instructions');

        $this->assertStringContainsString('<p style="text-align: center">Hi {{OWNER_NAME}}</p>', $stored);
        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('<iframe', $stored);
    }

    /**
     * A value written before the editor existed never passed the sanitizer — it
     * was stored by the old plain textarea. It is cleaned on the way out, so it
     * can no more print raw markup on the settings page or an invoice than a
     * value saved today.
     */
    public function test_a_value_stored_before_the_editor_existed_is_cleaned_on_the_way_out(): void
    {
        SettingsHelper::bulkSet([
            'invoice_instructions' => '<p>Pay at the office.</p><script>alert(1)</script>',
        ], CollectionSettingsQuery::GROUP);

        SettingsHelper::forgetCache(CollectionSettingsQuery::CACHE_KEY);

        $instructions = app(CollectionSettingsQuery::class)->get('invoice_instructions');

        $this->assertStringContainsString('<p>Pay at the office.</p>', $instructions);
        $this->assertStringNotContainsString('<script', $instructions);
    }

    public function test_the_instructions_print_their_placeholders_filled_in(): void
    {
        $this->save([
            'invoice_instructions' => '<p>Hi {{OWNER_NAME}}, please pay {{AMOUNT_DUE}}.</p>'
                . '<p>{{PAYMENT_METHODS_TABLE}}</p>',
        ]);

        $rendered = app(CollectionSettingsQuery::class)->renderInstructions([
            'OWNER_NAME' => 'Juan Dela Cruz',
            'AMOUNT_DUE' => '₱1,500.00',
        ]);

        $this->assertStringContainsString('Hi Juan Dela Cruz, please pay ₱1,500.00.', $rendered);
        $this->assertStringNotContainsString('{{', $rendered);
        $this->assertStringNotContainsString('PAYMENT_METHODS_TABLE', $rendered);
    }

    public function test_instructions_written_as_plain_text_keep_their_line_breaks(): void
    {
        // A line of plain text, the way a value saved before the editor existed
        // reads: no tags at all, only its own line breaks.
        $this->save(['invoice_instructions' => "Please pay at the office.\nThank you."]);

        $this->assertSame(
            "Please pay at the office.<br />\nThank you.",
            app(CollectionSettingsQuery::class)->renderInstructions()
        );
    }

    public function test_the_settings_page_edits_the_instructions_in_the_rich_text_editor(): void
    {
        $this->signIn();

        Livewire::test(CollectionsSettings::class)
            ->set('activeTab', 'payments')
            ->assertSee('data-rich-text="invoiceInstructions"', false);
    }

    /**
     * The field must not be declared with Summernote's own attribute name.
     *
     * Summernote keeps its state under jQuery's `data('summernote')` key, and
     * jQuery fills that key from the same-named HTML attribute — so an element
     * marked `data-summernote` is skipped by the plugin as "already built" and
     * the editor never appears. It is a silent failure: no error, no editor.
     */
    public function test_no_rich_text_field_is_marked_with_the_plugins_own_attribute_name(): void
    {
        $views = [
            resource_path('views/livewire/admin/settings/print-setting-form.blade.php'),
            resource_path('views/livewire/admin/settings/collections-settings.blade.php'),
        ];

        foreach ($views as $view) {
            $this->assertStringNotContainsString('data-summernote=', file_get_contents($view), $view);
        }
    }

    /**
     * The editor is only ever as good as the page it is on: the shared script
     * that builds every field, the plugin that script drives, and the tab that
     * carries the instruction field all have to reach the officer's browser
     * together (the field itself is on the page's second tab — see the test
     * above, which reads it where the officer edits it).
     */
    public function test_the_settings_page_serves_the_editor_and_its_assets(): void
    {
        $this->signIn();

        $response = $this->get(route('settings.collections'));

        $response->assertOk();
        $response->assertSee("getAttribute('data-rich-text')", false);
        $response->assertSee('vendor/summernote/summernote-bs4.min.js', false);
        $response->assertSee('vendor/summernote/summernote-bs4.min.css', false);
        $response->assertSee('Payments &amp; Receipts', false);
    }

    /**
     * What the office saved last is what the editor opens on — the field is
     * seeded with the stored HTML, not left blank for the officer to retype.
     */
    public function test_the_instructions_field_starts_from_what_is_stored(): void
    {
        $this->signIn();

        $this->save(['invoice_instructions' => '<p>Please settle at the office.</p>']);

        Livewire::test(CollectionsSettings::class)
            ->set('activeTab', 'payments')
            ->assertSee('<p>Please settle at the office.</p>', false);
    }
}
