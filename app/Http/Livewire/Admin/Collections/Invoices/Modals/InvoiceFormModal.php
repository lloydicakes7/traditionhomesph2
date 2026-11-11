<?php

namespace App\Http\Livewire\Admin\Collections\Invoices\Modals;

use App\Actions\Amenities\Bookings\CreateBookingInvoice;
use App\Actions\Amenities\Bookings\UpdateBookingInvoice;
use App\Actions\Collections\Invoices\CreateInvoice;
use App\Actions\Collections\Invoices\UpdateInvoice;
use App\Enums\BillType;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Models\Product\Product;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Collections\Invoices\InvoiceDetailQuery;
use App\Queries\Collections\Invoices\InvoiceListQuery;
use App\Queries\Collections\Products\ProductListQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;

/**
 * Add Invoice — one form for both ways an ad-hoc charge is raised: for a
 * single homeowner, or for every homeowners at once.
 *
 * Opened client-side by Bootstrap (`#invoiceFormModal`) with no id for a new
 * invoice and with the invoice's id on the trigger for an edit, and loaded
 * lazily by public/js/modal-loader.js. A page that already knows who is being
 * billed raises it through `openInvoiceFormModal` with the account, and an
 * amenity booking through `openBookingInvoiceFormModal` with the booking — see
 * openCreate() and openBookingInvoice().
 *
 * Two things make the form what it is:
 *   1. each line's price and amount fill in from the product the line is taken
 *      from, so the officer picks what is being billed and the arithmetic
 *      follows;
 *   2. an item the office has not set up yet can be added without leaving the
 *      invoice — the "+ New" link opens the Products form, and this modal picks
 *      the new item up from its `product-saved` event and adds it as a line.
 */
class InvoiceFormModal extends AdminModalComponent
{
    const MODAL_ID = 'invoiceFormModal';

    /** @var int|null Bill being edited; null when raising a new one */
    public $invoiceId = null;

    /** @var int|null Amenity booking the invoice belongs to */
    public $bookingId = null;

    /** @var bool Whether the amenity booking is for a guest */
    public $isGuest = false;

    /** @var string 'single' bills one homeowner, 'all' bills every homeowner */
    public $scope = 'single';

    /** @var array<string, mixed> The bill's own columns as form state */
    public $form = [
        'user_id'        => '',
        'billed_to_name' => '',
        'bill_date'      => '',
        'due_date'       => '',
        'discount'       => 0,
        'notes'          => '',
    ];

    /** @var array<int, array<string, mixed>> The lines being billed */
    public $items = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openInvoiceFormModal'        => 'openCreate',
        'openBookingInvoiceFormModal' => 'openBookingInvoice',
        'loadInvoiceFormModal'        => 'openEdit',
        'closeInvoiceFormModal'       => 'closeModal',
        'product-saved'               => 'handleProductSaved',
    ];

    /** @var InvoiceDetailQuery */
    protected $query;

    /** @var InvoiceListQuery */
    protected $invoices;

    /** @var ProductListQuery */
    protected $products;

    /** @var CreateInvoice */
    protected $createInvoice;

    /** @var UpdateInvoice */
    protected $updateInvoice;

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var CreateBookingInvoice */
    protected $createBookingInvoice;

    /** @var UpdateBookingInvoice */
    protected $updateBookingInvoice;

    public function boot(
        InvoiceDetailQuery $query,
        InvoiceListQuery $invoices,
        ProductListQuery $products,
        CreateInvoice $createInvoice,
        UpdateInvoice $updateInvoice,
        AmenityBookingListQuery $bookings,
        CreateBookingInvoice $createBookingInvoice,
        UpdateBookingInvoice $updateBookingInvoice
    ) {
        $this->query = $query;
        $this->invoices = $invoices;
        $this->products = $products;
        $this->createInvoice = $createInvoice;
        $this->updateInvoice = $updateInvoice;
        $this->bookings = $bookings;
        $this->createBookingInvoice = $createBookingInvoice;
        $this->updateBookingInvoice = $updateBookingInvoice;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when these run
    // =====================================================================

    /**
     * Open the form blank, for a new invoice.
     *
     * The account being billed is usually left for the officer to pick, but a
     * caller that already knows it — the requests page bills the homeowner a
     * request came from — hands it in and finds it filled in.
     *
     * @param  int|null  $userId  The account to bill, when the caller knows it
     */
    public function openCreate(?int $userId = null): void
    {
        $this->authorize('createInvoice', Bill::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->form['user_id'] = $userId ? (string) $userId : '';
        $this->form['bill_date'] = now()->format('Y-m-d');
        $this->form['due_date'] = now()->addDays(30)->format('Y-m-d');
        $this->items = [$this->blankItem()];

        $this->markLoaded();
    }

    public function openBookingInvoice(?int $bookingId = null): void
    {
        if (! $bookingId) {
            return;
        }

        $this->authorize('createInvoice', Bill::class);
        $this->authorize('recordPayment', AmenityBooking::class);

        $booking = $this->bookings->detail($bookingId);

        if ($booking->bill) {
            $this->openEdit($booking->bill_id);

            return;
        }

        if (! $booking->can_be_invoiced) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->resetModalState();
        $this->resetValidation();

        $this->bookingId = $booking->id;
        $this->scope = 'single';
        $this->isGuest = $booking->user_id === null;
        $this->form = [
            'user_id'        => (string) ($booking->user_id ?: ''),
            'billed_to_name' => $booking->user_id ? '' : (string) $booking->guest_name,
            'bill_date'      => now()->format('Y-m-d'),
            'due_date'       => now()->addWeek()->format('Y-m-d'),
            'discount'       => 0,
            'notes'          => 'Amenity reservation ' . $booking->reference,
        ];
        $this->items = $booking->invoiceLines();

        $this->markLoaded();
    }

    /**
     * Open the form loaded, for an existing invoice.
     *
     * @param  int|null  $id
     */
    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('updateInvoice', Bill::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->invoiceId = $id;

        $invoice = $this->query->findForEdit($this->invoiceId);
        $booking = $invoice->type === BillType::AMENITY_BOOKING
            ? $this->bookings->findByBill($invoice->id)
            : null;

        $this->bookingId = $booking ? $booking->id : null;
        $this->isGuest = $booking !== null && $invoice->user_id === null;
        $this->form = [
            'user_id'        => (string) ($invoice->user?->id ?: ''),
            'billed_to_name' => (string) ($invoice->billed_to_name ?: ''),
            'bill_date'      => $invoice->bill_date ? $invoice->bill_date->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date'       => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
            'discount'       => (float) $invoice->discount,
            'notes'          => (string) $invoice->notes,
        ];

        $this->items = $invoice->items->map(function ($item) {
            return [
                'product_id'  => (string) ($item->product_id ?: ''),
                'name'        => (string) $item->name,
                'description' => (string) $item->description,
                'quantity'    => (float) $item->quantity,
                'unit_price'  => (float) $item->unit_price,
                'amount'      => (float) $item->amount,
            ];
        })->all();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    /**
     * Return the form to a blank state.
     */
    private function resetModalState(): void
    {
        $this->invoiceId = null;
        $this->bookingId = null;
        $this->isGuest = false;
        $this->scope = 'single';
        $this->form = [
            'user_id'        => '',
            'billed_to_name' => '',
            'bill_date'      => '',
            'due_date'       => '',
            'discount'       => 0,
            'notes'          => '',
        ];
        $this->items = [];
    }

    // =====================================================================
    // Lines
    // =====================================================================

    /**
     * Add an empty line.
     */
    public function addItem(): void
    {
        if ($this->bookingId) {
            return;
        }

        $this->items[] = $this->blankItem();
    }

    /**
     * Drop a line.
     *
     * @param  int  $index
     */
    public function removeItem($index): void
    {
        if ($this->bookingId) {
            return;
        }

        unset($this->items[$index]);

        $this->items = array_values($this->items);
    }

    /**
     * A line's product or figures changed: re-price it from the product and
     * refresh what the line comes to.
     */
    public function updatedItems($value, $key): void
    {
        $parts = explode('.', (string) $key);

        if (count($parts) !== 2) {
            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if (! isset($this->items[$index])) {
            return;
        }

        if ($field === 'product_id') {
            $product = $value ? Product::find($value) : null;

            if ($product) {
                $this->items[$index]['name'] = $product->name;
                $this->items[$index]['description'] = (string) $product->description;
                $this->items[$index]['unit_price'] = (float) $product->price;
            }
        }

        $quantity = (float) ($this->items[$index]['quantity'] ?? 0);
        $unitPrice = (float) ($this->items[$index]['unit_price'] ?? 0);

        $this->items[$index]['amount'] = round($quantity * $unitPrice, 2);
    }


    /**
     * A new item was just added on the Products form: bill it straight away.
     *
     * @param  int|array  $payload
     */
    public function handleProductSaved($payload = null): void
    {
        if ($this->bookingId) {
            return;
        }

        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;

        $product = $id ? Product::find($id) : null;

        if (! $product) {
            return;
        }

        $this->items[] = [
            'product_id'  => (string) $product->id,
            'name'        => $product->name,
            'description' => (string) $product->description,
            'quantity'    => 1,
            'unit_price'  => (float) $product->price,
            'amount'      => (float) $product->price,
        ];

        $this->emit('notify', 'info', 'Added "' . $product->name . '" as a line on this invoice.');
    }

    /**
     * Open the Products form so an item can be added without leaving the
     * invoice. The form is a sibling component, so it is told to load its
     * blank state and its shell is shown with `modal-open`.
     */
    public function quickAddProduct(): void
    {
        if ($this->bookingId) {
            return;
        }

        $this->authorize('create', Product::class);

        $this->emit('openProductFormModal');
        $this->dispatchBrowserEvent('modal-open', ['id' => 'productFormModal']);
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->invoiceId
            ? $this->authorize('updateInvoice', Bill::class)
            : $this->authorize('createInvoice', Bill::class);

        if ($this->bookingId) {
            $this->authorize('recordPayment', AmenityBooking::class);
        }

        $this->validate($this->rules(), $this->messages());

        $payload = [
            'scope'          => $this->invoiceId ? 'single' : $this->scope,
            'user_id'        => $this->form['user_id'] ?: null,
            'billed_to_name' => $this->form['billed_to_name'] ?: null,
            'bill_date'      => $this->form['bill_date'],
            'due_date'       => $this->form['due_date'] ?: null,
            'discount'       => $this->form['discount'] ?: 0,
            'notes'          => $this->form['notes'] ?: null,
            'items'          => $this->items,
        ];

        if ($this->bookingId) {
            $this->saveBooking($payload);
        } else {
            $this->invoiceId ? $this->saveExisting($payload) : $this->raiseNew($payload);
        }

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function saveBooking(array $payload): void
    {
        $booking = $this->bookings->find($this->bookingId);
        $admin = auth('web')->user();

        if ($this->invoiceId) {
            $invoice = $this->updateBookingInvoice->handle($booking, $payload, $admin);
            $this->emit('notify', 'success', 'Bill ' . $invoice->reference . ' updated.');
        } else {
            $invoice = $this->createBookingInvoice->handle($booking, $payload, $admin);
            $this->emit('notify', 'success', 'Invoice ' . $invoice->reference . ' raised.');
        }

        $this->emit('invoice-saved', ['bill_ids' => [$invoice->id]]);
    }

    private function saveExisting(array $payload): void
    {
        $invoice = $this->updateInvoice->handle(
            $this->query->findForEdit($this->invoiceId),
            $payload,
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Bill ' . $invoice->reference . ' updated.');
        $this->emit('invoice-saved');
    }

    private function raiseNew(array $payload): void
    {
        $invoices = $this->createInvoice->handle($payload, auth('web')->user());

        $this->emit('notify', 'success', $invoices->count() > 1
            ? $invoices->count() . ' invoices raised.'
            : 'Bill ' . ($invoices->first()->reference ?? '') . ' raised.');

        $this->emit('invoice-saved', ['bill_ids' => $invoices->pluck('id')->all()]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * A blank line.
     *
     * @return array<string, mixed>
     */
    private function blankItem(): array
    {
        return [
            'product_id'  => '',
            'name'        => '',
            'description' => '',
            'quantity'    => 1,
            'unit_price'  => 0,
            'amount'      => 0,
        ];
    }

    /**
     * What the form accepts.
     *
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $booking = $this->bookingId !== null;

        return [
            'scope'              => ['required', Rule::in(['single', 'all'])],
            'form.user_id'       => $booking
                ? ['nullable', 'exists:users,id']
                : ['required_if:scope,single', 'nullable', 'exists:users,id'],
            'form.billed_to_name' => $booking && ! $this->form['user_id']
                ? ['required', 'string', 'max:150']
                : ['nullable', 'string', 'max:150'],
            'form.bill_date'     => ['required', 'date'],
            'form.due_date'      => ['nullable', 'date', 'after_or_equal:form.bill_date'],
            'form.discount'      => ['nullable', 'numeric', 'min:0'],
            'form.notes'         => ['nullable', 'string', 'max:1000'],
            'items'              => $booking ? ['nullable', 'array'] : ['required', 'array', 'min:1'],
            'items.*.product_id' => $booking ? ['nullable'] : ['required', 'exists:products,id'],
            'items.*.quantity'   => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Short, distinct messages — the field says what is wrong with it and
     * nothing more.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.user_id.required_if'     => 'Choose the homeowner being billed.',
            'form.billed_to_name.required' => 'Name who the invoice is billed to.',
            'form.due_date.after_or_equal' => 'The due date cannot be before the bill date.',
            'items.required'               => 'Add at least one item to bill.',
            'items.*.product_id.required'  => 'Pick an item for this line.',
            'items.*.quantity.required'    => 'Enter a quantity.',
            'items.*.unit_price.required'  => 'Enter a price.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'form.user_id'        => 'user',
            'form.billed_to_name' => 'billed to',
            'form.bill_date'      => 'bill date',
            'form.due_date'       => 'due date',
            'form.discount'       => 'discount',
            'form.notes'          => 'notes',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        if (! $this->loaded) {
            return view('livewire.admin.collections.invoices.modals.invoice-form-modal', [
                'users'           => collect(),
                'products'        => collect(),
                'currentBooking'  => null,
                'subtotal'        => 0.0,
                'total'           => 0.0,
                'subtotalDisplay' => MoneyHelper::format(0),
                'totalDisplay'    => MoneyHelper::format(0),
            ]);
        }

        $subtotal = 0.0;

        foreach ($this->items as $item) {
            $subtotal += (float) ($item['amount'] ?? 0);
        }

        $discount = (float) ($this->form['discount'] ?: 0);

        return view('livewire.admin.collections.invoices.modals.invoice-form-modal', [
            'users'           => $this->invoices->billableUsers(),
            'products'        => $this->bookingId ? collect() : $this->products->billable(),
            'currentBooking'  => $this->bookingId ? $this->bookings->detail($this->bookingId) : null,
            'subtotal'        => $subtotal,
            'total'           => round($subtotal - $discount, 2),
            'subtotalDisplay' => MoneyHelper::format($subtotal),
            'totalDisplay'    => MoneyHelper::format(round($subtotal - $discount, 2)),
        ]);
    }
}
