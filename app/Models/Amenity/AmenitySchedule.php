<?php

namespace App\Models\Amenity;

use App\Enums\Weekday;
use App\Utility\TimeHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * One weekday of an amenity's opening hours.
 *
 * A row with no times on file is a day the amenity is closed — closed days are
 * kept as rows so the week is always seven entries and a day never silently
 * disappears from the schedule.
 */
class AmenitySchedule extends Model
{
    protected $fillable = [
        'amenity_id',
        'day_of_week',
        'opens_at',
        'closes_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'day_of_week' => 'integer',
    ];

    /**
     * The amenity this row belongs to.
     */
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }

    /**
     * The Weekday enum instance for this row.
     */
    public function getDayEnumAttribute(): ?Weekday
    {
        return $this->day_of_week ? Weekday::tryFrom((int) $this->day_of_week) : null;
    }

    /**
     * Whether the amenity is closed on this day.
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->opens_at === null
            || $this->closes_at === null
            || $this->opens_at === ''
            || $this->closes_at === '';
    }

    /**
     * This day's opening window as a record writes it: "8:00 AM – 10:00 PM",
     * or "Closed".
     */
    public function getHoursDisplayAttribute(): string
    {
        if ($this->is_closed) {
            return 'Closed';
        }

        return TimeHelper::clockWindow($this->opens_at, $this->closes_at);
    }
}
