<?php

namespace App\Models\Bill;

use App\Models\Product\Product;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of a bill.
 *
 * A line keeps its own copy of the name and the price it was billed at, so the
 * record says what was charged even if the product behind it is re-priced or
 * removed. `amount` is the line's own total (quantity × unit price), written
 * when the line is saved.
 *
 * Every kind of bill is made of these: an invoice's charges, an amenity
 * reservation's rate and deposit, and a month's dues — where `unit_price` holds
 * the rate snapshot and `quantity` the area charged (a flat rate is one unit).
 */
class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'product_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
    ];

    /**
     * The bill this line belongs to.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * The product the line was taken from, when it came from one.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The unit price as a record writes it: "₱150.00".
     */
    public function getUnitPriceDisplayAttribute(): string
    {
        return MoneyHelper::format($this->unit_price);
    }

    /**
     * The line total as a record writes it: "₱300.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }

    /**
     * The quantity without trailing zeros: "2", "1.5".
     */
    public function getQuantityDisplayAttribute(): string
    {
        return rtrim(rtrim(number_format((float) $this->quantity, 2), '0'), '.') ?: '0';
    }
}
