<?php

namespace Tests\Feature;

use App\Enums\AdvanceDepositStatus;
use App\Enums\PaymentStatus;
use App\Http\Livewire\Admin\Collections\Modals\RecordPaymentModal;
use App\Http\Livewire\Admin\Collections\MonthlyDues\Modals\AdvancePaymentModal;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionsPaymentModalsTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): void
    {
        $role = Role::firstOrCreate(['name' => 'Administrator'], ['description' => 'Administrator', 'permissions' => []]);

        $admin = Admin::create([
            'name' => 'Officer',
            'email' => 'modal-check-' . uniqid() . '@example.test',
            'password' => Hash::make('Secret123!'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    private function bill(User $user): Bill
    {
        $bill = Bill::create([
            'reference' => 'INV-' . uniqid(),
            'user_id' => $user->id,
            'billed_to_name' => $user->name,
            'type' => 'invoice',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 1500,
            'total' => 1500,
            'paid_amount' => 0,
            'balance' => 1500,
            'status' => 'unpaid',
        ]);

        $bill->items()->create([
            'name' => 'Charge',
            'quantity' => 1,
            'unit_price' => 1500,
            'amount' => 1500,
        ]);

        return $bill;
    }

    public function test_the_advance_modal_renders_its_new_fields(): void
    {
        $this->signIn();

        Livewire::test(AdvancePaymentModal::class)
            ->emit('openAdvancePaymentModal')
            ->assertSee('Advance Deposit')
            ->assertSee('No advance deposit')
            ->assertSee('How to Pay')
            ->assertDontSee('advance-preview-');
    }

    public function test_the_payment_modal_settles_a_bill_from_the_homeowners_deposit(): void
    {
        $this->signIn();

        $user = factory(User::class)->create();
        $bill = $this->bill($user);

        $deposit = AdvanceDeposit::create([
            'user_id' => $user->id,
            'amount' => 2000,
            'balance' => 2000,
            'status' => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subWeek(),
        ]);

        Livewire::test(RecordPaymentModal::class)
            ->emit('openRecordPaymentModal')
            ->set('bill', (string) $bill->id)
            ->assertSee('Advance Deposit')
            ->set('advance_deposit_id', (string) $deposit->id)
            ->assertSet('amount', 1500.0)
            ->call('confirm')
            ->assertHasNoErrors();

        $this->assertSame(0.0, (float) $bill->fresh()->balance);
        $this->assertSame(500.0, (float) $deposit->fresh()->balance);
        $this->assertSame($deposit->id, (int) Payment::first()->advance_deposit_id);
        $this->assertSame(PaymentStatus::RECORDED, Payment::first()->status);
    }
}
