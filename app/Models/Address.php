<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'label',
        'street',
        'subdivision',
        'barangay',
        'city',
        'province',
        'region',
        'zip_code',
        'country',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * The association's own address — the one every unit stands in.
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    // =====================================================================
    // Display
    // =====================================================================

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->subdivision,
            $this->barangay,
            $this->city,
            $this->province,
            $this->region,
            $this->zip_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getStreetLineAttribute(): string
    {
        $parts = array_filter([$this->street, $this->subdivision]);
        return implode(', ', $parts);
    }

    public function getCityLineAttribute(): string
    {
        $parts = array_filter([$this->barangay, $this->city, $this->province, $this->zip_code]);
        return implode(', ', $parts);
    }

    public function getShortAddressAttribute(): string
    {
        $street = $this->street_line;
        $city   = $this->city_line;
        $parts  = array_filter([$street, $city]);
        return implode(', ', $parts);
    }
}
