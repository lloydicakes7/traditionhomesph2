<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Calendar;

use App\Enums\BookingStatus;
use App\Enums\Weekday;
use App\Http\Livewire\AdminComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;

/**
 * Calendar View page — one month of reservations, Monday first.
 *
 * The page is a view of the same data the bookings table lists, so it reads it
 * from the same query (`calendar()`) and opens the same dialogs the table does,
 * by id: a reservation chip is a Bootstrap trigger on
 * `#bookingViewModal` carrying the booking's id, and "New Booking" is one on
 * `#bookingFormModal`. No filtering happens here — the month is the whole
 * filter, and it is carried in the query string so a particular month can be
 * linked to.
 *
 * Only the days a facility is actually taken are on it: approved and completed
 * reservations, never a pending request or a cancelled one.
 */
class BookingCalendar extends AdminComponent
{
    use AuthorizesRequests;

    /** @var string The month on screen, as "Y-m" */
    public $month = '';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['month'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'booking-saved' => '$refresh',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    public function boot(AmenityBookingListQuery $bookings)
    {
        $this->bookings = $bookings;
    }

    /**
     * Land on the current month unless a valid month was linked to.
     */
    public function mount(): void
    {
        $this->month = $this->validMonth($this->month) ?? now()->format('Y-m');
    }

    // =====================================================================
    // Moving through the months
    // =====================================================================

    public function previousMonth(): void
    {
        $this->month = $this->current()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->current()->addMonth()->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = now()->format('Y-m');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', AmenityBooking::class);

        $calendar = $this->bookings->calendar($this->month);

        // The month is passed as `gridMonth`: `$month` is this component's own
        // public property (the "Y-m" on screen), and Livewire hands a view its
        // component's public properties, so a `month` view variable would be the
        // string rather than the date.
        return view('livewire.admin.amenities-booking.calendar.booking-calendar', [
            'gridMonth' => $calendar['month'],
            'weeks'     => $calendar['weeks'],
            'byDay'     => $calendar['byDay'],
            'weekdays'  => Weekday::shortLabels(),
            'statuses'  => array_values(array_filter(BookingStatus::cases(), function (BookingStatus $status) {
                return in_array($status->value, BookingStatus::committedValues(), true);
            })),
        ]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The month on screen as a date — a linked month that does not parse falls
     * back to the current one rather than breaking the page.
     */
    private function current(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month ?: now()->format('Y-m'))->startOfMonth();
    }

    /**
     * A valid "Y-m" month, or null.
     */
    private function validMonth(?string $month): ?string
    {
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        try {
            Carbon::createFromFormat('Y-m', $month);
        } catch (\Throwable $e) {
            return null;
        }

        return $month;
    }
}
