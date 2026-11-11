<?php

namespace App\Models\Amenity;

use App\Enums\AmenityRateType;
use App\Enums\Weekday;
use App\Utility\MoneyHelper;
use App\Utility\TimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * A bookable facility — the clubhouse, the pool, a court, a function hall.
 *
 * An amenity owns two rules for its own use, and every surface that prices,
 * schedules or offers it reads them from here:
 *
 *   1. what it charges (`rateFor()`) — per hour, or flat;
 *   2. when it can be used (`windowOn()`, `isOpenDuring()`) — its own weekly
 *      opening hours, one row per weekday.
 *
 * What a reservation asks for up front (its refundable deposit) is entered on
 * the reservation itself. The portal's view of an amenity is `is_bookable`: a
 * facility that is not bookable is administered but never offered. Every
 * reservation the portal files waits for the office to approve it.
 */
class Amenity extends Model
{
    /** @var string Where a facility's photograph is kept */
    const IMAGE_DIRECTORY = 'photos/amenities';

    protected $fillable = [
        'name',
        'rate_type',
        'rate',
        'is_bookable',
        'description',
        'image_path',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rate'        => 'decimal:2',
        'is_bookable' => 'boolean',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The opening hours of this amenity, one row per weekday.
     */
    public function schedules()
    {
        return $this->hasMany(AmenitySchedule::class)->orderBy('day_of_week');
    }

    /**
     * Every reservation filed for this amenity.
     */
    public function bookings()
    {
        return $this->hasMany(AmenityBooking::class);
    }

    // =====================================================================
    // The week the form edits
    // =====================================================================

    /**
     * A week with every day open, as the form's schedule grid starts on a
     * facility that has none yet.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function blankWeek(): array
    {
        $week = [];

        foreach (Weekday::cases() as $day) {
            $week[$day->value] = ['enabled' => true, 'opens_at' => '08:00', 'closes_at' => '17:00'];
        }

        return $week;
    }

    /**
     * This amenity's own opening hours as the form's schedule grid reads them.
     *
     * @return array<string, array<string, mixed>>
     */
    public function scheduleWeek(): array
    {
        $schedules = $this->schedules->keyBy('day_of_week');

        $week = [];

        foreach (Weekday::cases() as $day) {
            $schedule = $schedules->get($day->value);
            $isOpen = $schedule && ! $schedule->is_closed;

            $week[$day->value] = [
                'enabled'   => $isOpen,
                'opens_at'  => $isOpen ? substr($schedule->opens_at, 0, 5) : '',
                'closes_at' => $isOpen ? substr($schedule->closes_at, 0, 5) : '',
            ];
        }

        return $week;
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the facility's details.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Only the facilities a homeowner may request from the portal.
     */
    public function scopeBookable($query)
    {
        return $query->where('is_bookable', true);
    }

    /**
     * Order facilities by name — the ordering every list uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The AmenityRateType enum instance for this amenity's pricing model.
     */
    public function getRateTypeEnumAttribute(): ?AmenityRateType
    {
        return $this->rate_type ? AmenityRateType::tryFrom($this->rate_type) : null;
    }

    /**
     * URL of the facility's photo, or null when none was uploaded.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * Whether the association charges nothing for this facility.
     *
     * Not every amenity is paid for: a playground or a court is offered for
     * free, and a free booking never goes through the payment step.
     */
    public function getIsFreeAttribute(): bool
    {
        return (float) $this->rate <= 0;
    }

    /**
     * The rate as a card writes it: the amount plus what it buys —
     * "₱120 / hr", "₱5,000" for a flat rate, "Free" when nothing is charged.
     */
    public function getRateDisplayAttribute(): string
    {
        if ($this->is_free) {
            return 'Free';
        }

        return MoneyHelper::formatShort($this->rate)
            . ($this->rate_type_enum ? $this->rate_type_enum->unitSuffix() : '');
    }

    /**
     * What the rate is charged for, spelled out for the view modal —
     * "Fixed", "Hourly".
     */
    public function getRateBasisDisplayAttribute(): string
    {
        $type = $this->rate_type_enum;

        if (! $type) {
            return '—';
        }

        return $type->label();
    }

    /**
     * How the portal sees this amenity, as the view modal labels it.
     */
    public function getPortalStatusLabelAttribute(): string
    {
        return $this->is_bookable ? 'Bookable' : 'Not on the portal';
    }

    /**
     * What that means for a homeowner, as the view modal notes it.
     */
    public function getPortalStatusNoteAttribute(): string
    {
        return $this->is_bookable
            ? 'The amenity is offered to homeowners for booking through the portal.'
            : 'The office books this facility itself.';
    }

    /**
     * The weekly opening hours, Monday first, one entry per day — the row the
     * view modal lists, including the days the amenity is closed.
     *
     * @return array<int, array{day: string, hours: string, is_closed: bool}>
     */
    public function getScheduleRowsAttribute(): array
    {
        $schedules = $this->schedules->keyBy('day_of_week');

        $rows = [];

        foreach (Weekday::cases() as $day) {
            $schedule = $schedules->get($day->value);

            $rows[] = [
                'day'       => $day->label(),
                'hours'     => $schedule && ! $schedule->is_closed
                    ? TimeHelper::clockWindow($schedule->opens_at, $schedule->closes_at)
                    : 'Closed',
                'is_closed' => ! $schedule || $schedule->is_closed,
            ];
        }

        return $rows;
    }

    /**
     * Whether this amenity keeps any opening hours at all.
     */
    public function getHasScheduleAttribute(): bool
    {
        return $this->schedules->contains(function (AmenitySchedule $schedule) {
            return ! $schedule->is_closed;
        });
    }

    /**
     * The week in one line, consecutive days with the same hours grouped —
     * "Mon–Sun 8AM–10PM", or "Mon–Fri 8AM–10PM, Sat 10AM–6PM".
     */
    public function getScheduleSummaryAttribute(): string
    {
        if (! $this->has_schedule) {
            return 'No schedule set';
        }

        $schedules = $this->schedules->keyBy('day_of_week');

        $segments = [];
        $group = null;

        foreach (Weekday::cases() as $day) {
            $schedule = $schedules->get($day->value);

            $hours = $schedule && ! $schedule->is_closed
                ? TimeHelper::window($schedule->opens_at, $schedule->closes_at)
                : 'Closed';

            if ($group !== null && $group['hours'] === $hours) {
                $group['to'] = $day;

                continue;
            }

            if ($group !== null) {
                $segments[] = $group;
            }

            $group = ['from' => $day, 'to' => $day, 'hours' => $hours];
        }

        if ($group !== null) {
            $segments[] = $group;
        }

        return implode(', ', array_map(function (array $segment) {
            $days = $segment['from']->is($segment['to'])
                ? $segment['from']->shortLabel()
                : $segment['from']->shortLabel() . '–' . $segment['to']->shortLabel();

            return $days . ' ' . $segment['hours'];
        }, $segments));
    }

    // =====================================================================
    // Rates and opening hours — the rules this amenity is booked under
    // =====================================================================

    /**
     * What a booking of this window costs, under this amenity's own rate:
     * a flat rate is charged once, an hourly rate per hour booked.
     *
     * @param  mixed  $startsAt
     * @param  mixed  $endsAt
     * @return float
     */
    public function rateFor($startsAt, $endsAt): float
    {
        $type = $this->rate_type_enum;
        $hours = $this->billableHours($startsAt, $endsAt);

        if ($type && $type->is(AmenityRateType::HOURLY())) {
            return round((float) $this->rate * $hours, 2);
        }

        return round((float) $this->rate, 2);
    }

    /**
     * Hours a window bills for, in decimal hours.
     *
     * @param  mixed  $startsAt
     * @param  mixed  $endsAt
     * @return float
     */
    public function billableHours($startsAt, $endsAt): float
    {
        $minutes = Carbon::parse($startsAt)->diffInMinutes(Carbon::parse($endsAt));

        return round($minutes / 60, 2);
    }

    /**
     * The opening window on the day a booking starts, or null when the amenity
     * is closed that day. Both ends are real times on that date, ready to
     * compare against a booking.
     *
     * @param  mixed  $date
     * @return array{opens_at: \Illuminate\Support\Carbon, closes_at: \Illuminate\Support\Carbon}|null
     */
    public function windowOn($date): ?array
    {
        $schedule = $this->schedules->firstWhere('day_of_week', Weekday::of($date)->value);

        if (! $schedule || $schedule->is_closed) {
            return null;
        }

        $day = Carbon::parse($date)->startOfDay();

        return [
            'opens_at'  => $day->copy()->setTimeFromTimeString($schedule->opens_at),
            'closes_at' => $day->copy()->setTimeFromTimeString($schedule->closes_at),
        ];
    }

    /**
     * Whether a window falls inside the amenity's opening hours on that day.
     *
     * @param  mixed  $startsAt
     * @param  mixed  $endsAt
     * @return bool
     */
    public function isOpenDuring($startsAt, $endsAt): bool
    {
        $window = $this->windowOn($startsAt);

        if (! $window) {
            return false;
        }

        $startsAt = Carbon::parse($startsAt);
        $endsAt = Carbon::parse($endsAt);

        return $startsAt->lt($endsAt)
            && $startsAt->gte($window['opens_at'])
            && $endsAt->lte($window['closes_at']);
    }
}
