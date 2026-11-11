<?php

namespace App\Models\Account;

use App\Models\Expense\ExpenseCategory;
use App\Models\JournalEntry\JournalLine;
use App\Models\PaymentMethod;
use App\Models\Product\ProductCategory;
use App\Casts\EnumCast;
use App\Enums\AccountKind;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of the association's chart of accounts.
 *
 * The office works with three kinds — income, expenses and cash — while the
 * books still store the accounting type behind them. An account may be removed
 * only while nothing has been posted to it and no category or cash path points
 * at it; otherwise it stays, because the postings made against it are part of
 * the books (a journal line keeps the account's name as a snapshot).
 *
 * What an account's balance *is* comes from its postings, never from a stored
 * column: the money columns of the chart and the reports are all computed from
 * `journal_lines` (see the Queries under \App\Queries\Reports), so an account
 * can never disagree with the entries behind it.
 */
class Account extends Model
{
    protected $fillable = [
        'code',
        'system_key',
        'name',
        'type',
        'description',
        'is_cash',
    ];

    protected $casts = [
        'type'    => EnumCast::class . ':' . AccountType::class,
        'is_cash' => 'boolean',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * Every line posted against this account.
     */
    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * The payment methods whose money lands in this account.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * The expense categories whose spending is charged to this account.
     */
    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * The product categories whose billing earns into this account.
     */
    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    /**
     * The transfers that moved money out of this account.
     */
    public function transfersFrom()
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    /**
     * The transfers that moved money into this account.
     */
    public function transfersTo()
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }


    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * The chart's own order: by code, which is grouped by kind by design
     * (1xxx money, 4xxx income, 5xxx expense).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('code');
    }

    /**
     * Only the accounts money sits in.
     */
    public function scopeCash($query)
    {
        return $query->where('is_cash', true);
    }

    /**
     * Only the accounts of one type.
     */
    public function scopeOfType($query, AccountType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeNotSystem($query)
    {
        return $query->where(function ($query) {
            $query->where('is_cash', true)
                ->orWhereIn('type', [AccountType::INCOME, AccountType::EXPENSE]);
        });
    }

    /**
     * Free-text search across the code and the name.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('code', 'like', $like)
                ->orWhere('name', 'like', $like);
        });
    }

    // =====================================================================
    // The chart's own numbering and shape
    // =====================================================================

    /**
     * The next free code in a kind's block, stepped by ten.
     */
    public static function nextCode(AccountKind $kind): string
    {
        $base = $kind->baseCode();
        $highest = (int) static::query()
            ->where('code', 'like', substr((string) $base, 0, 1) . '%')
            ->max('code');

        $next = max($highest + 10, $base + 10);

        return str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The account a category posts to, created on first use.
     *
     * A category the office adds is also a line of the chart: income a new
     * product category earns and spending a new expense category is charged to
     * each need an account of its own. The one it already points at is renamed
     * with the category instead of being abandoned, so the books keep one
     * account per category rather than one per name it has been given.
     */
    public static function syncCategory(string $name, AccountType $type, ?Account $existing = null): Account
    {
        $name = trim($name);

        if ($existing) {
            if ($existing->name !== $name) {
                $existing->update(['name' => $name]);
            }

            return $existing;
        }

        $kind = $type->is(AccountType::EXPENSE()) ? AccountKind::EXPENSE() : AccountKind::INCOME();

        return static::create([
            'code'        => static::nextCode($kind),
            'name'        => $name,
            'type'        => $kind->type(),
            'description' => null,
            'is_cash'     => false,
        ]);
    }
    // =====================================================================
    // Which account a posting lands in
    // =====================================================================

    /** @var string Stable key of the cash the association holds on hand */
    const KEY_CASH_ON_HAND = 'cash_on_hand';

    /** @var string Stable key of the recurring monthly income account */
    const KEY_DUES_INCOME = 'dues_income';

    /** @var string Stable key of the one-off fees income account */
    const KEY_FEES_INCOME = 'fees_income';

    /** @var string Stable key of the amenity income account */
    const KEY_AMENITY_INCOME = 'amenity_income';

    /** @var string Stable key of the penalty income account */
    const KEY_PENALTY_INCOME = 'penalty_income';

    /** @var string Stable key of the discount account */
    const KEY_DISCOUNT = 'discount';

    /** @var string Stable key of the unclassified expense account */
    const KEY_UNCLASSIFIED_EXPENSE = 'unclassified_expense';

    /** @var string The money the association holds on hand */
    const CASH_CODE = '1000';

    /** @var string Association dues — the recurring monthly income */
    const DUES_INCOME_CODE = '4000';

    /** @var string Fees, clearances, IDs — every one-off charge */
    const FEES_INCOME_CODE = '4100';

    /** @var string Income earned renting out the association's amenities */
    const AMENITY_INCOME_CODE = '4110';

    /** @var string Late payment penalties charged on a bill */
    const PENALTY_INCOME_CODE = '4200';

    /** @var string Discounts and adjustments allowed on a bill (a contra account) */
    const DISCOUNT_CODE = '4900';

    /** @var string What the association owes its suppliers */
    const PAYABLE_CODE = '2000';

    /** @var string Spending the office has not filed under a category */
    const UNCLASSIFIED_EXPENSE_CODE = '5100';

    /**
     * The money the association holds in its own hands.
     */
    public static function cashOnHand(): Account
    {
        return static::bySystemKey(
            self::KEY_CASH_ON_HAND,
            self::CASH_CODE,
            AccountType::ASSET(),
            'Cash on Hand',
            true
        );
    }

    /**
     * The recurring monthly income account.
     */
    public static function duesIncome(): Account
    {
        return static::bySystemKey(
            self::KEY_DUES_INCOME,
            self::DUES_INCOME_CODE,
            AccountType::INCOME(),
            'Monthly Dues'
        );
    }

    /**
     * Fees, clearances and IDs — where a charge with no category of its own
     * earns its income.
     */
    public static function feesIncome(): Account
    {
        return static::bySystemKey(
            self::KEY_FEES_INCOME,
            self::FEES_INCOME_CODE,
            AccountType::INCOME(),
            'Fees and Charges'
        );
    }

    /**
     * Where an amenity reservation earns its income when it is billed.
     */
    public static function amenityIncome(): Account
    {
        return static::bySystemKey(
            self::KEY_AMENITY_INCOME,
            self::AMENITY_INCOME_CODE,
            AccountType::INCOME(),
            'Amenities'
        );
    }

    /**
     * Where a late-payment penalty is booked as income.
     */
    public static function penaltyIncome(): Account
    {
        return static::bySystemKey(
            self::KEY_PENALTY_INCOME,
            self::PENALTY_INCOME_CODE,
            AccountType::INCOME(),
            'Penalties'
        );
    }

    /**
     * The contra account a discount allowed on a bill is charged to.
     */
    public static function discounts(): Account
    {
        return static::bySystemKey(
            self::KEY_DISCOUNT,
            self::DISCOUNT_CODE,
            AccountType::INCOME(),
            'Discounts'
        );
    }

    /**
     * Where spending with no category of its own is booked.
     */
    public static function unclassifiedExpense(): Account
    {
        return static::bySystemKey(
            self::KEY_UNCLASSIFIED_EXPENSE,
            self::UNCLASSIFIED_EXPENSE_CODE,
            AccountType::EXPENSE(),
            'Others'
        );
    }

    /**
     * Where money paid through this method lands. A method the office has not
     * mapped takes the association's cash on hand, which is what an unmapped —
     * usually over-the-counter — receipt is.
     */
    public static function forPaymentMethod(?PaymentMethod $method): Account
    {
        if ($method && $method->account) {
            return $method->account;
        }

        return static::cashOnHand();
    }

    /**
     * The account spending in this category is charged to.
     */
    public static function forExpenseCategory(?ExpenseCategory $category): Account
    {
        if ($category && $category->account) {
            return $category->account;
        }

        return static::unclassifiedExpense();
    }

    /**
     * The revenue account an item in this category earns when it is billed.
     */
    public static function forProductCategory(?ProductCategory $category): Account
    {
        if ($category && $category->account) {
            return $category->account;
        }

        return static::feesIncome();
    }

    /**
     * Whether this account is the association's own cash box.
     */
    public function isCashBox(): bool
    {
        return $this->system_key === self::KEY_CASH_ON_HAND;
    }

    /**
     * Whether this is the protected default cash account. Its name may change,
     * but it cannot be removed or turned into another kind.
     */
    public function isDefaultCash(): bool
    {
        return $this->system_key === self::KEY_CASH_ON_HAND;
    }

    /**
     * The kind the office sees: money, income, or expense. Null means a system
     * account — an equity or liability line the books keep for themselves.
     */
    public function kind(): ?AccountKind
    {
        if ($this->is_cash) {
            return AccountKind::MONEY();
        }

        if ($this->type && $this->type->is(AccountType::INCOME())) {
            return AccountKind::INCOME();
        }

        if ($this->type && $this->type->is(AccountType::EXPENSE())) {
            return AccountKind::EXPENSE();
        }

        return null;
    }

    /**
     * Whether this is a system account, read-only in the chart.
     */
    public function isSystem(): bool
    {
        return $this->kind() === null;
    }

    /**
     * The category page that owns this account's name, when one does.
     */
    public function managedByLabel(): ?string
    {
        if ($this->productCategories()->exists()) {
            return 'the product category page';
        }

        if ($this->expenseCategories()->exists()) {
            return 'the expense category page';
        }

        return null;
    }

    /**
     * Why this account cannot be removed, if it cannot.
     *
     * Only an account with no postings and no category, product, money path or
     * transfer pointing at it may go. A journal line is not nulled away here:
     * an account the books already name stays.
     */
    public function deletionBlocker(): ?string
    {
        if ($this->isSystem()) {
            return 'System accounts are kept by the books and cannot be removed.';
        }

        if ($this->isDefaultCash()) {
            return 'Cash on Hand is the association\'s default cash account and cannot be removed. You can rename it.';
        }

        $entries = $this->journalLines()->distinct()->count('journal_entry_id');

        if ($entries > 0) {
            return $entries . ' journal entry(ies) are posted to this account — an account in the books cannot be removed.';
        }

        if ($this->productCategories()->exists()) {
            return 'A product category is billed into this account — rename it on the product category page.';
        }

        if ($this->expenseCategories()->exists()) {
            return 'An expense category is charged to this account — rename it on the expense category page.';
        }

        if ($this->paymentMethods()->exists()) {
            return 'A payment method records money into this account — point it elsewhere first.';
        }

        $transfers = $this->transfersFrom()->count() + $this->transfersTo()->count();

        if ($transfers > 0) {
            return $transfers . ' recorded transfer(s) name this account, and a transfer is part of the books — it cannot be removed.';
        }

        return null;
    }

    /**
     * Find a system account by its stable key, or put it on the chart.
     */
    private static function bySystemKey(
        string $key,
        string $code,
        AccountType $type,
        string $name,
        bool $isCash = false
    ): Account
    {
        return static::firstOrCreate(
            ['system_key' => $key],
            [
                'code'    => $code,
                'name'    => $name,
                'type'    => $type->value,
                'is_cash' => $isCash,
            ]
        );
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * How a select writes the account: "1010 · BPI Checking — Operating".
     */
    public function getLabelAttribute(): string
    {
        return $this->code . ' · ' . $this->name;
    }

    /**
     * The type's label, safe when the stored value is unknown.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type ? $this->type->label() : '—';
    }

    /**
     * The office-facing kind label, or System for a structural account.
     */
    public function getKindLabelAttribute(): string
    {
        $kind = $this->kind();

        return $kind ? $kind->label() : 'System';
    }

    /**
     * The type's badge theme, safe when the stored value is unknown.
     */
    public function getTypeBadgeAttribute(): string
    {
        return $this->type ? $this->type->badgeTheme() : 'secondary';
    }

    /**
     * The side this account normally carries its balance on ("debit"|"credit").
     */
    public function normalSide(): string
    {
        return $this->type ? $this->type->normalSide() : 'debit';
    }

    /**
     * Whether this account normally carries a debit balance.
     */
    public function isDebitNormal(): bool
    {
        return $this->normalSide() === 'debit';
    }

    /**
     * The amount side of the entry, decided the way a ledger writes it: a
     * debit-normal account carries the net of debits less credits as a debit.
     *
     * @param  float  $debits
     * @param  float  $credits
     * @return array{side: string, amount: float}
     */
    public function sideOf(float $debits, float $credits): array
    {
        $net = round($debits - $credits, 2);

        if ($this->isDebitNormal()) {
            return [
                'side'   => $net >= 0 ? 'debit' : 'credit',
                'amount' => abs($net),
            ];
        }

        $net = round($credits - $debits, 2);

        return [
            'side'   => $net >= 0 ? 'credit' : 'debit',
            'amount' => abs($net),
        ];
    }
}
