<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Collections\Payments\PaymentTableList;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_officer_previews_and_downloads_a_recorded_receipt(): void
    {
        $admin = $this->admin('Administrator');
        $payment = $this->payment();

        $preview = $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.show', $payment));

        $preview->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $preview->headers->get('content-type')));

        $download = $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.download', $payment));

        $download->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $download->headers->get('content-type')));
        $this->assertStringContainsString('Official-Receipt-OR-000001.pdf', (string) $download->headers->get('content-disposition'));
    }

    public function test_an_officer_prints_and_downloads_the_thermal_receipt(): void
    {
        $admin = $this->admin('Administrator');
        $payment = $this->payment();

        $preview = $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.thermal.show', $payment));

        $preview->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $preview->headers->get('content-type')));

        $download = $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.thermal.download', $payment));

        $download->assertOk();
        $this->assertStringContainsString('attachment', strtolower((string) $download->headers->get('content-disposition')));
    }

    public function test_the_thermal_receipt_keeps_the_payment_facts(): void
    {
        $payment = $this->payment();

        $html = view('pdf.receipt-thermal', [
            'receipt'   => app(\App\Queries\Collections\Payments\ReceiptQuery::class)->build($payment),
            'hoa_name'  => 'Tradition Homes',
            'printedAt' => now(),
        ])->render();

        $this->assertStringContainsString('OR-000001', $html);
        $this->assertStringContainsString('Juan Cruz', $html);
        $this->assertStringContainsString('₱500.00', $html);
        $this->assertStringContainsString('Five Hundred Pesos Only', $html);
    }

    public function test_the_a4_receipt_fits_on_one_page(): void
    {
        $payment = $this->payment();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', [
            'receipt'    => app(\App\Queries\Collections\Payments\ReceiptQuery::class)->build($payment),
            'letterhead' => [],
            'styles'     => '',
            'printedAt'  => now(),
        ]);

        $pdf->render();

        $this->assertSame(1, $pdf->getCanvas()->get_page_count());
    }

    public function test_the_receipt_carries_the_payment_facts_and_nothing_private(): void
    {
        $admin = $this->admin('Administrator');
        $payment = $this->payment();

        $html = view('pdf.receipt', [
            'receipt'    => app(\App\Queries\Collections\Payments\ReceiptQuery::class)->build($payment),
            'letterhead' => [],
            'styles'     => '',
            'printedAt'  => now(),
        ])->render();

        $this->assertStringContainsString('OR-000001', $html);
        $this->assertStringContainsString('Juan Cruz', $html);
        $this->assertStringContainsString('₱500.00', $html);
        $this->assertStringContainsString('Five Hundred Pesos Only', $html);
        $this->assertStringContainsString('Not valid without HOA Dry Seal.', $html);
        $this->assertStringNotContainsString('philsys', strtolower($html));
        $this->assertStringNotContainsString((string) $admin->email, $html);
    }

    public function test_an_officer_without_the_payments_permission_is_denied(): void
    {
        $admin = $this->admin('Staff');
        $payment = $this->payment();

        $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.show', $payment))
            ->assertForbidden();
    }

    public function test_a_pending_receipt_has_no_receipt_to_print(): void
    {
        $admin = $this->admin('Administrator');
        $payment = $this->payment('pending', null);

        $this->actingAs($admin, 'web')
            ->get(route('collections.payments.receipt.show', $payment))
            ->assertNotFound();
    }

    public function test_the_payments_list_offers_the_receipt_of_a_recorded_row_only(): void
    {
        $admin = $this->admin('Administrator');
        $recorded = $this->payment();
        $pending = $this->payment('pending', null);

        Livewire::actingAs($admin, 'web')
            ->test(PaymentTableList::class)
            ->assertSee(route('collections.payments.receipt.show', $recorded), false)
            ->assertDontSee(route('collections.payments.receipt.show', $pending), false);
    }

    public function test_a_homeowner_cannot_reach_the_admin_receipt(): void
    {
        $payment = $this->payment();

        $this->actingAs(factory(User::class)->create(), 'homeowner')
            ->get(route('collections.payments.receipt.show', $payment))
            ->assertRedirect();
    }

    // ===== Helpers =====

    private function admin(string $roleName): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['description' => $roleName, 'permissions' => []]
        );

        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'receipt-admin-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);
    }

    private function payment(string $status = 'recorded', ?string $receiptNo = 'OR-000001'): Payment
    {
        $bill = Bill::create([
            'reference'      => 'INV-' . uniqid(),
            'user_id'        => factory(User::class)->create()->id,
            'billed_to_name' => 'Juan Cruz',
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'subtotal'       => 1500,
            'total'          => 1500,
            'paid_amount'    => 0,
            'balance'        => 1500,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Monthly Dues',
            'quantity'   => 1,
            'unit_price' => 1500,
            'amount'     => 1500,
        ]);

        return Payment::create([
            'bill_id'    => $bill->id,
            'user_id'    => $bill->user_id,
            'amount'     => 500,
            'paid_at'    => now(),
            'status'     => $status,
            'receipt_no' => $receiptNo,
        ]);
    }
}
