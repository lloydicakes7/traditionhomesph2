<?php

namespace App\Models;

use App\Models\Account\Account;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A way the association takes money — cash, GCash, a bank transfer.
 *
 * The method is office-managed data: its name, the instructions printed beside
 * it on an invoice, an optional QR code, and whether it is still offered. What
 * makes it a table rather than an enum is exactly that — the office adds an
 * account or retires one without a release.
 */
class PaymentMethod extends Model
{
    /** @var string Public-disk directory payment QR codes live in */
    const QR_DIRECTORY = 'photos/payment-methods';

    /**
     * What the ledger calls money taken by hand.
     *
     * The office receives cash over the counter as well as through the online
     * accounts it manages, and most receipts are exactly that, so the label
     * stands in for a method rather than being one: the record payment dialogs
     * offer it as their own default choice and \App\Models\Payment writes it on
     * a receipt that named no method. Nothing matches it against the table, so
     * the office may keep its online accounts alone in there.
     *
     * @var string
     */
    const IN_PERSON_LABEL = 'In-person payment';

    protected $fillable = [
        'name',
        'details',
        'qr_path',
        'is_active',
        'sort_order',
        'account_id',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The payments received through this method.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The cash or bank account money paid this way lands in.
     *
     * A receipt is posted against this account (see
     * \App\Models\Account\Account::forPaymentMethod()), which is
     * what makes the books' cash accounts agree with the money the association
     * actually took in.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the method's name and instructions.
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
                ->orWhere('details', 'like', $like);
        });
    }

    /**
     * Only the methods still offered to homeowners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * The order the methods are listed in — the office's own, then by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * URL of the uploaded QR code, or null when none is on file.
     */
    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_path ? Storage::disk('public')->url($this->qr_path) : null;
    }
}
