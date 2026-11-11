<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\BookForAdmin;
use App\Actions\Amenities\Bookings\UpdateBooking;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Amenities\AmenityListQuery;
use App\Queries\Amenities\BookingWindowQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;

/**
 * "Add / Edit Booking" modal — one form for taking a reservation at the
 * counter and for correcting one already on the books.
 */
class BookingFormModal extends AdminModalComponent
{
    /** @var bool Whether editing an existing reservation */
    public $isEditing = false;

    /** @var int|null ID of the reservation being edited */
    public $editingBookingId = null;

    /** @var bool Whether the reservation is for someone outside the association */
    public $isGuest = false;

    /** @var array Form fields */
    public $form = [
        'amenity_id'     => '',
        'booker_type'    => 'homeowner',
        'user_id'        => '',
        'guest_name'     => '',
        'date'           => '',
        'start_time'     => '08:00',
        'end_time'       => '12:00',
        'deposit_amount' => '',
        'purpose'        => '',
        'remarks'        => '',
    ];

    /** @var array<string, string> Modal open / load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openBookingFormModal'  => 'openCreate',
        'loadBookingFormModal'  => 'openEdit',
        'closeBookingFormModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var AmenityListQuery */
    protected $amenities;

    /** @var BookingWindowQuery */
    protected $bookingRules;

    /** @var BookForAdmin */
    protected $bookForAdmin;

    /** @var UpdateBooking */
    protected $updateBooking;

    public function boot(
        AmenityBookingListQuery $bookings,
        AmenityListQuery $amenities,
        BookingWindowQuery $bookingRules,
        BookForAdmin $bookForAdmin,
        UpdateBooking $updateBooking
    ) {
        $this->bookings = $bookings;
        $this->amenities = $amenities;
        $this->bookingRules = $bookingRules;
        $this->bookForAdmin = $bookForAdmin;
        $this->updateBooking = $updateBooking;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.amenity_id'     => ['required', Rule::exists('amenities', 'id')],
            'form.booker_type'    => ['required', Rule::in(['homeowner', 'guest'])],
            'form.user_id'        => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->form['booker_type'] === 'homeowner';
                }),
                Rule::exists('users', 'id'),
            ],
            'form.guest_name'     => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->form['booker_type'] === 'guest';
                }),
                'string',
                'max:150',
            ],
            'form.date'           => ['required', 'date_format:Y-m-d'],
            'form.start_time'     => ['required', 'date_format:H:i'],
            'form.end_time'       => ['required', 'date_format:H:i'],
            'form.deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.purpose'        => ['nullable', 'string', 'max:500'],
            'form.remarks'        => ['nullable', 'string', 'max:500'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', AmenityBooking::class);

        $this->isEditing = false;
        $this->editingBookingId = null;
        $this->isGuest = false;
        $this->form = [
            'amenity_id'     => '',
            'booker_type'    => 'homeowner',
            'user_id'        => '',
            'guest_name'     => '',
            'date'           => now()->toDateString(),
            'start_time'     => '08:00',
            'end_time'       => '12:00',
            'deposit_amount' => '',
            'purpose'        => '',
            'remarks'        => '',
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        $this->isEditing = true;
        $this->editingBookingId = $booking->id;
        $this->isGuest = ! $booking->user_id;
        $this->form = [
            'amenity_id'     => $booking->amenity_id,
            'booker_type'    => $booking->user_id ? 'homeowner' : 'guest',
            'user_id'        => $booking->user_id,
            'guest_name'     => $booking->guest_name,
            'date'           => $booking->starts_at->toDateString(),
            'start_time'     => $booking->starts_at->format('H:i'),
            'end_time'       => $booking->ends_at->format('H:i'),
            'deposit_amount' => $booking->deposit_amount,
            'purpose'        => $booking->purpose,
            'remarks'        => $booking->remarks,
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the reservation when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->editingBookingId = null;
        $this->isGuest = false;

        parent::resetModal();
    }

    /**
     * The booker switch flipped: the form follows it, so validation and the saved
     * record both see the kind of booker the officer chose.
     */
    public function updatedIsGuest($value): void
    {
        $this->form['booker_type'] = $value ? 'guest' : 'homeowner';
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', AmenityBooking::class);
        $this->validate();

        $amenity = $this->amenities->detail((int) $this->form['amenity_id']);
        [$startsAt, $endsAt] = $this->bookingRules->windowFrom($this->form);

        $error = $this->bookingRules->windowError(
            $amenity,
            $startsAt,
            $endsAt,
            $this->isEditing ? $this->editingBookingId : null
        );

        if ($error) {
            $this->addError('form.start_time', $error);

            return;
        }

        if ($this->isEditing) {
            $this->updateBooking->handle(
                $this->bookings->find($this->editingBookingId),
                $this->bookingData(),
                auth('web')->user()
            );
        } else {
            $this->bookForAdmin->handle($this->bookingData(), auth('web')->user());
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Booking updated successfully.'
                : 'Booking added successfully.');

        $this->emit('booking-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'bookingFormModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $selects = $this->loaded
            ? ['amenities' => $this->amenities->options(), 'owners' => $this->bookings->owners()]
            : ['amenities' => [], 'owners' => collect()];

        $preview = $this->loaded ? $this->bookingRules->preview($this->form) : null;

        return view('livewire.admin.amenities-booking.bookings.modals.booking-form-modal', $selects + [
            'amountPreview'  => $preview ? MoneyHelper::format($preview['amount']) : null,
            'depositPreview' => $preview ? MoneyHelper::format($preview['deposit']) : null,
            'totalPreview'   => $preview ? MoneyHelper::format($preview['amount'] + $preview['deposit']) : null,
            'currencySymbol' => MoneyHelper::symbol(),
        ]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The form as the Action writes it: only the input that matches the booker
     * switch is sent, so switching from a homeowner to a guest never leaves the
     * account behind.
     */
    private function bookingData(): array
    {
        $data = $this->form;

        if ($data['booker_type'] === 'guest') {
            $data['user_id'] = null;
        }

        return $data;
    }
}
