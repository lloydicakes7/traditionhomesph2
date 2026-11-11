<?php

namespace App\Http\Livewire\Portal\Bookings\Modals;

use App\Actions\Amenities\Bookings\RequestBooking;
use App\Http\Livewire\PortalModalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Queries\Amenities\AmenityListQuery;
use App\Queries\Amenities\BookingWindowQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;

/**
 * "Request a Booking" modal — the homeowner reserving an amenity from the
 * portal.
 *
 * Opened by native Bootstrap JS: the list's button carries `data-toggle`, and
 * `public/js/modal-loader.js` turns that into `openBookingRequestModal`. The
 * shell is on screen at the click; the blank form is rendered on that load.
 * Emits `booking-requested` after a request is filed, which refreshes the list.
 *
 * A portal request is exactly the office's own booking through the same
 * service, with three differences the portal owns:
 *   — only amenities the office has opened to the portal are offered, and
 *     `AmenityPolicy::book()` is what says so;
 *   — the reservation is filed for the account's own resident, never for
 *     somebody else;
 *   — it cannot be back-dated, because a homeowner requests what is coming.
 * Every reservation waits for the office to approve it: no facility is used
 * without an officer saying so.
 */
class BookingRequestModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'bookingRequestModal';

    /** @var array Form fields */
    public $form = [
        'amenity_id' => '',
        'date'       => '',
        'start_time' => '08:00',
        'end_time'   => '12:00',
        'purpose'    => '',
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openBookingRequestModal'  => 'open',
        'closeBookingRequestModal' => 'closeModal',
    ];

    /** @var AmenityListQuery */
    protected $amenities;

    /** @var BookingWindowQuery */
    protected $bookingRules;

    /** @var RequestBooking */
    protected $requestBooking;

    public function boot(
        AmenityListQuery $amenities,
        BookingWindowQuery $bookingRules,
        RequestBooking $requestBooking
    ) {
        $this->amenities = $amenities;
        $this->bookingRules = $bookingRules;
        $this->requestBooking = $requestBooking;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.amenity_id' => ['required', Rule::exists('amenities', 'id')],
            'form.date'       => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'form.start_time' => ['required', 'date_format:H:i'],
            'form.end_time'   => ['required', 'date_format:H:i'],
            'form.purpose'    => ['nullable', 'string', 'max:500'],
        ];
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    public function open(): void
    {
        $this->form = $this->emptyForm();

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the form when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->form = $this->emptyForm();

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Request
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->validate();

        $amenity = $this->amenities->detail((int) $this->form['amenity_id']);

        // Only the facilities the office has opened to the portal can be booked.
        $this->authorize('book', $amenity);

        [$startsAt, $endsAt] = $this->bookingRules->windowFrom($this->form);

        $error = $this->bookingRules->windowError($amenity, $startsAt, $endsAt);

        if ($error) {
            $this->addError('form.start_time', $error);

            return;
        }

        $this->requestBooking->handle($amenity, $this->form, $this->account());

        $this->emit('notify', 'success', 'Booking requested — the office will review it and get back to you.');

        $this->emit('booking-requested');

        // Hide through Bootstrap; the loader's hidden event then drops the form.
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // Not loaded, the dialog shows only its skeleton: there is no point
        // asking for the bookable facilities it will not show yet. The view
        // still receives the empty shapes so it never reads an undefined
        // variable while the content is hidden.
        if (! $this->loaded) {
            return view('livewire.portal.bookings.modals.booking-request-modal', [
                'amenities' => collect(),
                'selected'  => null,
                'preview'   => null,
            ]);
        }

        $amenities = $this->amenities->bookable();
        $selected = $amenities->firstWhere('id', (int) $this->form['amenity_id']);

        $preview = null;

        if ($selected && $this->form['date'] && $this->form['start_time'] && $this->form['end_time']) {
            [$startsAt, $endsAt] = $this->bookingRules->windowFrom($this->form);

            $preview = [
                'amount' => $selected->is_free
                    ? 'Free'
                    : MoneyHelper::format($selected->rateFor($startsAt, $endsAt)),
            ];
        }

        return view('livewire.portal.bookings.modals.booking-request-modal', [
            'amenities' => $amenities,
            'selected'  => $selected,
            'preview'   => $preview,
        ]);
    }

    /**
     * A blank form, used to open the dialog and to reset it on close.
     *
     * @return array
     */
    private function emptyForm(): array
    {
        return [
            'amenity_id' => '',
            'date'       => now()->toDateString(),
            'start_time' => '08:00',
            'end_time'   => '12:00',
            'purpose'    => '',
        ];
    }
}
