<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * What every document's builder shares: the parameters it is handed, the
 * resident behind them, and the records a paper document is filled in from.
 *
 * A builder never reads a parameter raw. It validates first — the rules live on
 * the document itself (`PrintDocument::rules()`), so the print page's form and
 * the builder that draws the paper can never disagree about what is required —
 * and then reads the loaded models through the small coercion helpers here.
 *
 * Loading a resident is one query: the household, the unit, its address and the
 * member records all come back eager-loaded, so a document that prints a whole
 * household (or, for the residents list, every household) never falls into a
 * query per row.
 */
abstract class AbstractPrintDocumentBuilder implements PrintDocumentBuilder
{
    /**
     * The relationships a resident is printed with.
     *
     * `household` is reached through the member record and `unit()` falls back
     * to the household's own account, so both paths are loaded — and
     * `memberRecords.resident` is loaded because a household's own records are
     * not reached through the resident's membership row.
     *
     * @var array<int, string>
     */
    const RESIDENT_RELATIONS = [
        'user',
        'household.property.address',
        'household.user.resident',
        'household.memberRecords.resident',
    ];

    /** @var \App\Enums\PrintDocument */
    protected $document;

    public function __construct(PrintDocument $document)
    {
        $this->document = $document;
    }

    /**
     * The orientation the document is laid out in.
     */
    public function orientation(): string
    {
        return 'portrait';
    }

    /**
     * Validate the parameters, then draw the document.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function build(array $params): array
    {
        return $this->compose($this->validate($params));
    }

    /**
     * The document's own view-model, from parameters already validated.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    abstract protected function compose(array $params): array;

    /**
     * The document exactly as the paper prints it with nothing filled in.
     *
     * Only the documents that can be handed out empty carry one; a builder that
     * returns an empty array has no blank paper to print.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [];
    }

    /**
     * The parameters this document carries, as the document itself states them.
     *
     * A parameter the form did not send, and that the document does not
     * require, simply stays absent — a document prints a blank line for what it
     * was not told, and never a guess.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(array $params): array
    {
        return Validator::make($params, $this->document->rules())->validate();
    }

    // =====================================================================
    // The resident behind the document
    // =====================================================================

    /**
     * The resident the document is about, with everything it prints loaded.
     */
    protected function resident($residentId): ?Resident
    {
        if (! $residentId) {
            return null;
        }

        return Resident::query()
            ->with(self::RESIDENT_RELATIONS)
            ->find((int) $residentId);
    }

    /**
     * The household a resident belongs to — by their own membership row, or
     * through the portal account that holds the unit.
     */
    protected function householdOf(?Resident $resident): ?Household
    {
        if (! $resident) {
            return null;
        }

        $household = $resident->household ?: ($resident->user ? $resident->user->household : null);

        if ($household && ! $household->relationLoaded('memberRecords')) {
            $household->load('memberRecords.resident', 'property.address', 'user.resident');
        }

        return $household;
    }

    /**
     * The unit a resident lives in, or null while none is on file.
     */
    protected function propertyOf(?Resident $resident): ?Property
    {
        if (! $resident) {
            return null;
        }

        $household = $this->householdOf($resident);

        return ($household && $household->property) ? $household->property : $resident->unit();
    }

    /**
     * The household's members, the head first.
     *
     * The head is the account holder's own resident — the household's records
     * carry them too, so they are taken first and skipped in the records that
     * follow rather than printed twice.
     *
     * @return \Illuminate\Support\Collection<int, array{resident: \App\Models\Resident, relationship: string}>
     */
    protected function membersOf(?Household $household): Collection
    {
        $members = collect();

        if (! $household) {
            return $members;
        }

        $head = $household->head_resident;

        if ($head) {
            $members->push([
                'resident'     => $head,
                'relationship' => $household->headMember?->relationship_label ?? 'Head',
            ]);
        }

        foreach ($household->memberRecords as $record) {
            $resident = $record->resident;

            if (! $resident || ($head && $resident->id === $head->id)) {
                continue;
            }

            $members->push([
                'resident'     => $resident,
                'relationship' => $this->relationship($record->relationship),
            ]);
        }

        return $members;
    }

    /**
     * A member's relationship to the head, as a table writes it.
     */
    protected function relationship($relationship): string
    {
        $relationship = trim((string) $relationship);

        return $relationship === '' ? '—' : ucfirst($relationship);
    }

    /**
     * The household's head, or null while the unit has no account holder.
     */
    protected function headOf(?Household $household): ?Resident
    {
        return $household ? $household->head_resident : null;
    }

    // =====================================================================
    // Parameters
    // =====================================================================

    /**
     * An id parameter, or null when it was not sent.
     *
     * @param  array<string, mixed>  $params
     */
    protected function intOrNull(array $params, string $key): ?int
    {
        $value = $params[$key] ?? null;

        return (is_numeric($value) && (int) $value > 0) ? (int) $value : null;
    }

    /**
     * A text parameter, trimmed — never null, so a document prints an empty
     * line rather than the word "null".
     *
     * @param  array<string, mixed>  $params
     */
    protected function stringOf(array $params, string $key, string $default = ''): string
    {
        $value = trim((string) ($params[$key] ?? ''));

        return $value === '' ? $default : $value;
    }

    /**
     * A yes/no parameter, however the form spelled it.
     *
     * @param  array<string, mixed>  $params
     */
    protected function flag(array $params, string $key): bool
    {
        $value = $params[$key] ?? false;

        return $value === true || in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * A date parameter read as a date, defaulting to the day it is printed.
     *
     * @param  array<string, mixed>  $params
     */
    protected function dateOf(array $params, string $key, $default = null): ?Carbon
    {
        $value = $params[$key] ?? null;

        if ($value === null || $value === '') {
            return $default === null ? null : $this->carbon($default);
        }

        return $this->carbon($value);
    }

    /**
     * A value read as a date, or null when it is empty or unreadable.
     *
     * @param  mixed  $value
     */
    protected function carbon($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * An integer parameter, or null — a worker count that was not given.
     *
     * @param  array<string, mixed>  $params
     */
    protected function numberOf(array $params, string $key): ?int
    {
        $value = $params[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }
}
