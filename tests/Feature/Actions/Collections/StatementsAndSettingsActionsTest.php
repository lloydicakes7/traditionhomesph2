<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\Settings\UpdateCollectionSettings;
use App\Actions\Collections\Soa\DeleteStatementsOfAccount;
use App\Actions\Collections\Soa\PrepareStatementsOfAccount;
use App\Actions\Collections\Soa\SendStatementsOfAccount;
use App\Enums\SoaStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\SoaEmail;
use App\Models\User;
use App\Notifications\StatementOfAccountNotification;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StatementsAndSettingsActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Prepare =====

    public function test_preparing_statements_picks_the_accounts_with_an_open_bill(): void
    {
        $admin = $this->admin();

        $billed = factory(User::class)->create(['email' => 'billed@example.test']);
        $this->openBill($billed, 1200);

        factory(User::class)->create(['email' => 'nothing@example.test']);

        $prepared = app(PrepareStatementsOfAccount::class)->handle($this->period(), $admin);

        $this->assertSame(1, $prepared);
        $this->assertDatabaseHas('soa_emails', [
            'user_id' => $billed->id,
            'period'  => $this->period(),
            'status'  => SoaStatus::PENDING,
        ]);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'prepared',
            'module'   => 'soa_email',
        ]);

        // The same account is not prepared twice for the same month.
        $this->assertSame(0, app(PrepareStatementsOfAccount::class)->handle($this->period(), $admin));
    }

    // ===== Send =====

    public function test_sending_a_statement_marks_it_sent(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $user = factory(User::class)->create(['email' => 'statement@example.test']);
        $this->openBill($user, 1200);

        app(PrepareStatementsOfAccount::class)->handle($this->period(), $admin);

        $statement = SoaEmail::firstOrFail();

        $result = app(SendStatementsOfAccount::class)->handle([$statement->id], $admin);

        $this->assertSame(['sent' => 1, 'failed' => 0], $result);
        $this->assertSame(SoaStatus::SENT, $statement->fresh()->status);
        $this->assertNotNull($statement->fresh()->sent_at);
        Notification::assertSentTo($user, StatementOfAccountNotification::class);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'sent',
            'module'   => 'soa_email',
        ]);
    }

    // ===== Delete =====

    public function test_deleting_statements_removes_them(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create(['email' => 'delete@example.test']);
        $this->openBill($user, 1200);

        app(PrepareStatementsOfAccount::class)->handle($this->period(), $admin);

        $this->assertSame(1, app(DeleteStatementsOfAccount::class)->handle([SoaEmail::firstOrFail()->id], $admin));
        $this->assertSame(0, SoaEmail::count());
    }

    // ===== Settings =====

    public function test_saving_the_collection_settings_persists_and_logs(): void
    {
        $admin = $this->admin();

        app(UpdateCollectionSettings::class)->handle([
            'billing_method'    => 'fixed_rate',
            'generation_day'    => 5,
            'grace_period_days' => 10,
            'penalty_value'     => 250,
        ], 'Updated the monthly dues and billing settings', $admin);

        $settings = app(CollectionSettingsQuery::class);

        $this->assertSame(5, (int) $settings->get('generation_day'));
        $this->assertSame(10, (int) $settings->get('grace_period_days'));
        $this->assertSame(250.0, (float) $settings->get('penalty_value'));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id'    => $admin->id,
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => 'Updated the monthly dues and billing settings',
        ]);
    }

    public function test_the_instructions_are_sanitized_when_they_are_saved(): void
    {
        app(UpdateCollectionSettings::class)->handle([
            'invoice_instructions' => '<p>Hi {{OWNER_NAME}}</p><script>alert(1)</script>',
        ], 'Updated the invoice and receipt instructions', $this->admin());

        $stored = app(CollectionSettingsQuery::class)->get('invoice_instructions');

        $this->assertStringContainsString('<p>Hi {{OWNER_NAME}}</p>', $stored);
        $this->assertStringNotContainsString('<script', $stored);
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'soa-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function period(): string
    {
        return now()->format('Y-m');
    }

    private function openBill(User $user, float $total): Bill
    {
        $bill = Bill::create([
            'reference'      => 'INV-' . uniqid(),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance'        => $total,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Charge',
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $bill;
    }
}
