<?php

namespace Tests\Feature;

use App\Actions\Support\Requests\AttachRequestInvoice;
use App\Enums\PaymentStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Livewire\Admin\Collections\Invoices\Modals\InvoiceFormModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestApproveModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestCancelModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestCompleteModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestDeleteModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestFormModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestPaymentsModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestViewModal;
use App\Http\Livewire\Admin\Support\Requests\RequestTableList;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Request as RequestModel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The office's Requests page: one dropdown per row, whose moves follow the
 * request's own status, the delete that leaves the list at once, the invoice a
 * row raises for its homeowner, and the office filing a request of its own.
 *
 * The status rule is the whole of the first part: View, Create Invoice and
 * Delete are always there, Print and Complete appear once the request is
 * approved, and Edit/Approve/Cancel only while it is still pending.
 */
class SupportRequestsPageTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Row actions
    // =====================================================================

    public function test_a_pending_request_offers_the_moves_the_office_still_has(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);

        $list = Livewire::test(RequestTableList::class);

        // Always there, whatever the status.
        $list->assertSee('data-target="#requestViewModal"', false);
        $list->assertSee('data-target="#requestDeleteModal"', false);
        $list->assertSee('Create Invoice');

        // The office has still to decide, so the deciding moves are offered…
        $list->assertSee('data-target="#requestEditModal"', false);
        $list->assertSee('data-target="#requestApproveModal"', false);
        $list->assertSee('data-target="#requestCancelModal"', false);

        // …and the document is not issued before that decision.
        $list->assertDontSee('request-approve-print');
        $list->assertDontSee('<i class="fas fa-fw fa-print mr-1"></i>', false);
    }

    public function test_an_approved_request_shows_print_and_hides_the_deciding_moves(): void
    {
        $this->signInAdmin();

        $approved = $this->request(RequestType::CERTIFICATION, RequestStatus::APPROVED);

        $list = Livewire::test(RequestTableList::class);

        $list->assertSee('Print');
        $list->assertSee('data-target="#requestCompleteModal"', false);
        $list->assertSee('request=' . $approved->id, false);

        $list->assertDontSee('data-target="#requestEditModal"', false);
        $list->assertDontSee('data-target="#requestApproveModal"', false);
        $list->assertDontSee('data-target="#requestCancelModal"', false);

        // View, Create Invoice and Delete stay open on a decided request.
        $list->assertSee('data-target="#requestViewModal"', false);
        $list->assertSee('data-target="#requestDeleteModal"', false);
        $list->assertSee('Create Invoice');
    }

    public function test_a_cancelled_request_keeps_only_the_always_on_moves(): void
    {
        $this->signInAdmin();

        $this->request(RequestType::CERTIFICATION, RequestStatus::CANCELLED);

        $list = Livewire::test(RequestTableList::class);

        $list->assertSee('data-target="#requestViewModal"', false);
        $list->assertSee('data-target="#requestDeleteModal"', false);
        $list->assertSee('Create Invoice');

        $list->assertDontSee('data-target="#requestApproveModal"', false);
        $list->assertDontSee('data-target="#requestCancelModal"', false);
        $list->assertDontSee('data-target="#requestCompleteModal"', false);
        $list->assertDontSee('data-target="#requestEditModal"', false);
    }

    public function test_the_view_dialog_shows_print_only_once_the_request_is_approved(): void
    {
        $this->signInAdmin();

        $pending = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);
        $approved = $this->request(RequestType::CERTIFICATION, RequestStatus::APPROVED);

        // The whole record is always readable; the document only once the
        // office has issued it by approving the request.
        Livewire::test(RequestViewModal::class)
            ->call('load', $pending->id)
            ->assertSee('Certification')
            ->assertDontSee('fa-print');

        Livewire::test(RequestViewModal::class)
            ->call('load', $approved->id)
            ->assertSee('fa-print')
            ->assertSee('request=' . $approved->id, false);
    }

    // =====================================================================
    // Deciding — approve / complete / cancel
    // =====================================================================

    public function test_approving_a_pending_request_decides_it_and_opens_the_documents_window(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);

        Livewire::test(RequestApproveModal::class)
            ->call('load', $request->id)
            ->call('approve')
            ->assertEmitted('request-saved');

        $request->refresh();

        // The decision is written, and with it the document's release window:
        // approving is what makes the document downloadable later.
        $this->assertSame(RequestStatus::APPROVED, $request->status->value);
        $this->assertNotNull($request->document_expires_at);
        $this->assertTrue($request->document_expires_at->isFuture());
    }

    public function test_cancelling_a_pending_request_keeps_the_reason(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);

        Livewire::test(RequestCancelModal::class)
            ->call('load', $request->id)
            ->set('remarks', 'Incomplete requirements.')
            ->call('cancel')
            ->assertEmitted('request-saved');

        $request->refresh();

        $this->assertSame(RequestStatus::CANCELLED, $request->status->value);
        $this->assertSame('Incomplete requirements.', $request->remarks);
        // A cancelled request issues no document, so it opens no window.
        $this->assertNull($request->document_expires_at);
    }

    public function test_completing_an_approved_request_closes_it(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::APPROVED);

        Livewire::test(RequestCompleteModal::class)
            ->call('load', $request->id)
            ->call('complete')
            ->assertEmitted('request-saved');

        $request->refresh();

        $this->assertSame(RequestStatus::COMPLETED, $request->status->value);
        $this->assertNotNull($request->completed_at);
    }

    public function test_a_pending_request_cannot_be_completed(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);

        Livewire::test(RequestCompleteModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);

        $this->assertSame(RequestStatus::PENDING, $request->fresh()->status->value);
    }

    // =====================================================================
    // Deleting
    // =====================================================================

    public function test_deleting_a_request_takes_the_row_out_of_the_list(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING, 'Delacruz');

        $list = Livewire::test(RequestTableList::class);
        $list->assertSee('Delacruz');

        // The dialog is what deletes, and it announces it to the list.
        Livewire::test(RequestDeleteModal::class)
            ->call('load', $request->id)
            ->call('delete')
            ->assertEmitted('request-saved')
            ->assertDispatchedBrowserEvent('modal-close');

        $this->assertDatabaseMissing('requests', ['id' => $request->id]);

        // The list hears that and re-reads itself, so the row leaves without
        // the officer touching the browser. Whether the browser then removes
        // the row hangs on the row being keyed apart from the ids inside it —
        // see tests/Feature/SupportListRowKeyTest.php.
        $list->emit('request-saved');
        $list->assertDontSee('Delacruz');
    }

    public function test_cancelling_the_delete_dialog_announces_nothing(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::CERTIFICATION, RequestStatus::PENDING);

        Livewire::test(RequestDeleteModal::class)
            ->call('load', $request->id)
            ->call('closeModal')
            ->assertNotEmitted('request-saved');

        $this->assertDatabaseHas('requests', ['id' => $request->id]);
    }

    // =====================================================================
    // Create Invoice
    // =====================================================================

    public function test_a_row_raises_an_invoice_for_the_homeowners_own_account(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::PENDING, null, $account->resident);

        Livewire::test(RequestTableList::class)
            ->call('createInvoice', $request->id)
            ->assertEmitted('openInvoiceFormModal', $account->id)
            ->assertDispatchedBrowserEvent('modal-open');
    }

    public function test_the_invoice_form_opens_with_the_requests_homeowner_filled_in(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();

        // What the row's Create Invoice action hands the Invoices page's own
        // form: the account is already named, the officer adds the lines.
        Livewire::test(InvoiceFormModal::class)
            ->emit('openInvoiceFormModal', $account->id)
            ->assertSet('loaded', true)
            ->assertSet('form.user_id', (string) $account->id);
    }

    public function test_a_homeowner_without_an_account_has_nothing_to_bill(): void
    {
        $this->signInAdmin();

        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::PENDING);

        Livewire::test(RequestTableList::class)
            ->call('createInvoice', $request->id)
            ->assertNotEmitted('openInvoiceFormModal');

        $this->assertSame(0, Bill::count());
    }

    public function test_the_invoice_a_row_raises_is_linked_back_to_the_request(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::PENDING, null, $account->resident);

        $bill = Bill::create([
            'reference'      => 'INV-2026-0001',
            'user_id'        => $account->id,
            'billed_to_name' => $account->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 100,
            'total'          => 100,
            'balance'        => 100,
            'status'         => 'unpaid',
        ]);

        // The row remembers which request sent the officer to the bill form…
        $list = Livewire::test(RequestTableList::class)
            ->call('createInvoice', $request->id)
            ->assertSet('invoiceRequestId', $request->id);

        // …and links the bill the form announces back to it.
        $list->emit('invoice-saved', ['bill_ids' => [$bill->id]]);

        $this->assertSame($bill->id, (int) $request->fresh()->bill_id);

        // The row now says it is invoiced and no longer offers to bill it again.
        Livewire::test(RequestTableList::class)
            ->assertSee('Invoiced')
            ->assertDontSee('Create Invoice');
    }

    public function test_a_request_that_already_has_an_invoice_is_not_billed_twice(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::PENDING, null, $account->resident);

        $bill = Bill::create([
            'reference'      => 'INV-2026-0002',
            'user_id'        => $account->id,
            'billed_to_name' => $account->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 100,
            'total'          => 100,
            'balance'        => 100,
            'status'         => 'unpaid',
        ]);

        $request->update(['bill_id' => $bill->id]);

        // A second link is refused rather than quietly swapping the invoice.
        app(AttachRequestInvoice::class)->handle($request->fresh(), 999, auth('web')->user());
        $this->assertSame($bill->id, (int) $request->fresh()->bill_id);

        Livewire::test(RequestTableList::class)->assertDontSee('Create Invoice');
    }

    // =====================================================================
    // Tracking the invoice — the Payment column and the Payments dialog
    // =====================================================================

    public function test_the_payment_column_reads_the_invoices_own_status(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::APPROVED, null, $account->resident);

        // Nothing raised for it yet.
        Livewire::test(RequestTableList::class)->assertSee('Not billed');

        $bill = $this->billFor($account, 'INV-2026-0101');
        $request->update(['bill_id' => $bill->id]);

        // Raised but not settled.
        Livewire::test(RequestTableList::class)
            ->assertSee('INV-2026-0101')
            ->assertSee('Unpaid');

        // Settled: the column reads Paid.
        $bill->update(['status' => 'paid', 'paid_amount' => 100, 'balance' => 0]);

        Livewire::test(RequestTableList::class)->assertSee('Paid');
    }

    public function test_a_billed_request_offers_the_payments_action(): void
    {
        $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::APPROVED, null, $account->resident);

        // Nothing to track until the office has raised the invoice.
        Livewire::test(RequestTableList::class)->assertDontSee('requestPaymentsModal');

        $bill = $this->billFor($account, 'INV-2026-0102');
        $request->update(['bill_id' => $bill->id]);

        Livewire::test(RequestTableList::class)
            ->assertSee('Payments')
            ->assertSee('data-target="#requestPaymentsModal" data-id="' . $request->id . '"', false);
    }

    public function test_the_payments_dialog_records_a_waiting_portal_payment(): void
    {
        $admin = $this->signInAdmin();

        $account = $this->portalAccount();
        $request = $this->request(RequestType::HOA_PERMIT, RequestStatus::APPROVED, null, $account->resident);
        $bill = $this->billFor($account, 'INV-2026-0103');
        $request->update(['bill_id' => $bill->id]);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $account->id,
            'amount'  => 100,
            'paid_at' => now(),
            'status'  => PaymentStatus::PENDING,
        ]);

        Livewire::test(RequestPaymentsModal::class)
            ->call('load', $request->id)
            ->assertSee('INV-2026-0103')
            ->call('approve', $payment->id)
            ->assertEmitted('request-saved');

        $payment->refresh();
        $bill->refresh();

        // The ledger's own act: the receipt is recorded and the bill settles.
        $this->assertSame(PaymentStatus::RECORDED, $payment->status);
        $this->assertSame($admin->id, (int) $payment->received_by_admin_id);
        $this->assertSame('paid', $bill->status);
    }

    // =====================================================================
    // The office's own filing
    // =====================================================================

    public function test_the_office_files_a_request_for_a_homeowner(): void
    {
        $this->signInAdmin();

        $resident = factory(Resident::class)->create(['surname' => 'Bautista', 'first_name' => 'Jun']);

        Livewire::test(RequestFormModal::class)
            ->emit('openRequestFormModal')
            ->set('form.resident_id', $resident->id)
            ->set('form.type', RequestType::BUSINESS_PERMIT)
            ->set('form.business_name', 'Sari-Sari Store')
            ->set('form.nature_of_business', 'Retail')
            ->set('form.hours_of_operation', '8:00 AM – 8:00 PM')
            ->set('form.purpose', 'business_permit')
            ->call('save')
            ->assertEmitted('request-saved')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('requests', [
            'resident_id'   => $resident->id,
            'type'          => RequestType::BUSINESS_PERMIT,
            'status'        => RequestStatus::PENDING,
            'business_name' => 'Sari-Sari Store',
        ]);
    }

    public function test_the_office_cannot_file_a_request_without_naming_a_homeowner(): void
    {
        $this->signInAdmin();

        Livewire::test(RequestFormModal::class)
            ->emit('openRequestFormModal')
            ->set('form.type', RequestType::MEMBERSHIP_FORM)
            ->call('save')
            ->assertHasErrors(['form.resident_id' => 'required']);

        $this->assertSame(0, RequestModel::count());
    }

    public function test_the_page_offers_the_office_its_own_filing_form(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('support.requests'));

        $response->assertOk();
        $response->assertSee('New Request');
        $response->assertSee('data-target="#requestFormModal"', false);
        $response->assertSee('id="requestFormModal"', false);

        // Every dialog the page's rows can open is mounted once, beside the
        // list — including the two the Create Invoice action uses (the
        // Collections bill form and the Products form it can add a line from).
        $response->assertSee('id="requestApproveModal"', false);
        $response->assertSee('id="requestCompleteModal"', false);
        $response->assertSee('id="requestCancelModal"', false);
        $response->assertSee('id="requestDeleteModal"', false);
        $response->assertSee('id="requestPaymentsModal"', false);
        $response->assertSee('id="invoiceFormModal"', false);
        $response->assertSee('id="productFormModal"', false);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'requests-page-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * A homeowner account, with the resident it is tied to.
     */
    private function portalAccount(): User
    {
        $resident = factory(Resident::class)->create();

        return factory(User::class)->create(['resident_id' => $resident->id]);
    }

    private function request(
        string $type,
        string $status,
        ?string $surname = null,
        ?Resident $resident = null
    ): RequestModel {
        $resident = $resident ?: factory(Resident::class)->create([
            'surname'    => $surname ?: 'Reyes',
            'first_name' => 'Ana',
        ]);

        return RequestModel::create([
            'resident_id' => $resident->id,
            'type'        => $type,
            'purpose'     => $type === RequestType::CERTIFICATION ? 'residency' : null,
            'status'      => $status,
        ]);
    }

    /**
     * An invoice for a homeowner's account, with the one line a recalculation
     * reads its total back from.
     */
    private function billFor(User $account, string $reference): Bill
    {
        $bill = Bill::create([
            'reference'      => $reference,
            'user_id'        => $account->id,
            'billed_to_name' => $account->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 100,
            'total'          => 100,
            'balance'        => 100,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Charge ' . $reference,
            'quantity'   => 1,
            'unit_price' => 100,
            'amount'     => 100,
        ]);

        return $bill;
    }
}
