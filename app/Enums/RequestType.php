<?php

namespace App\Enums;

/**
 * What a homeowner asks the office to issue.
 *
 * The five documents the association issues on request: a Membership Form, a
 * Business Permit, an HOA Permit, an Endorsement Letter and a Certification. A
 * fixed set — the portal's form offers these and nothing else — which is why it
 * is an enum rather than an office-managed lookup table.
 *
 * The RBI forms are deliberately not of them: they are the association's own
 * report to the barangay, printed by the office from the RBI Forms page, not a
 * document a homeowner requests.
 *
 * Each type also states the fields it asks for (through the `has*` helpers) and
 * the printable document it turns into (`printDocument()`), so the form, the
 * validation, the list and the print route all read one source rather than each
 * re-deriving the type's shape.
 *
 * The Membership Form asks for nothing beyond the request itself: it is filled
 * in from the homeowner's own profile record (see \App\Models\Resident), so
 * every `has*` helper answers false for it and the form simply files the
 * request.
 *
 * @method static RequestType MEMBERSHIP_FORM()
 * @method static RequestType BUSINESS_PERMIT()
 * @method static RequestType HOA_PERMIT()
 * @method static RequestType ENDORSEMENT_LETTER()
 * @method static RequestType CERTIFICATION()
 */
class RequestType extends Enum
{
    const MEMBERSHIP_FORM    = 'membership_form';
    const BUSINESS_PERMIT    = 'business_permit';
    const HOA_PERMIT         = 'hoa_permit';
    const ENDORSEMENT_LETTER = 'endorsement_letter';
    const CERTIFICATION      = 'certification';

    /**
     * Map of value → human-readable label, in the order the form offers them.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MEMBERSHIP_FORM    => 'Membership Form',
            self::BUSINESS_PERMIT    => 'Business Permit',
            self::HOA_PERMIT         => 'HOA Permit',
            self::ENDORSEMENT_LETTER => 'Endorsement Letter',
            self::CERTIFICATION      => 'Certification',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The Font Awesome icon the form and the list use.
     */
    public function icon(): string
    {
        return [
            self::MEMBERSHIP_FORM    => 'fas fa-fw fa-users',
            self::BUSINESS_PERMIT    => 'fas fa-fw fa-store',
            self::HOA_PERMIT         => 'fas fa-fw fa-hard-hat',
            self::ENDORSEMENT_LETTER => 'fas fa-fw fa-envelope-open-text',
            self::CERTIFICATION      => 'fas fa-fw fa-certificate',
        ][$this->value] ?? 'fas fa-fw fa-clipboard';
    }

    /**
     * Whether this type asks for a purpose.
     */
    public function hasPurpose(): bool
    {
        return in_array($this->value, [self::CERTIFICATION, self::BUSINESS_PERMIT], true);
    }

    /**
     * The purpose enum this type draws its choices from, or null when the type
     * has no purpose.
     *
     * @return class-string<\App\Enums\Enum>|null
     */
    public function purposeEnum(): ?string
    {
        return [
            self::CERTIFICATION   => CertificationPurpose::class,
            self::BUSINESS_PERMIT => BusinessPermitPurpose::class,
        ][$this->value] ?? null;
    }

    /**
     * The purpose choices as value → label, in the type's own order.
     *
     * @return array<string, string>
     */
    public function purposeOptions(): array
    {
        $enum = $this->purposeEnum();

        return $enum ? $enum::labels() : [];
    }

    /**
     * Whether the purpose's own written-out text is part of this type — only a
     * business permit's "Other" needs it.
     */
    public function hasPurposeOther(): bool
    {
        return $this->value === self::BUSINESS_PERMIT;
    }

    /**
     * Whether this type asks for the home-based business details.
     */
    public function hasBusinessDetails(): bool
    {
        return $this->value === self::BUSINESS_PERMIT;
    }

    /**
     * Whether this type asks for the permit type and its scope of work.
     */
    public function hasPermitDetails(): bool
    {
        return $this->value === self::HOA_PERMIT;
    }

    /**
     * Whether this type is addressed to somebody.
     */
    public function hasAddressee(): bool
    {
        return $this->value === self::ENDORSEMENT_LETTER;
    }

    /**
     * Whether the document this type issues is filled in from the homeowner's
     * own profile record rather than from anything typed into the form. The
     * form says so, and the list summarises the request the same way.
     */
    public function usesProfileRecord(): bool
    {
        return $this->value === self::MEMBERSHIP_FORM;
    }

    /**
     * The Print Documents sheet this request turns into.
     */
    public function printDocument(): ?PrintDocument
    {
        return [
            self::MEMBERSHIP_FORM    => PrintDocument::MEMBERSHIP_FORM(),
            self::BUSINESS_PERMIT    => PrintDocument::BUSINESS_PERMIT(),
            self::HOA_PERMIT         => PrintDocument::HOA_PERMIT(),
            self::ENDORSEMENT_LETTER => PrintDocument::ENDORSEMENT_LETTER(),
            self::CERTIFICATION      => PrintDocument::CERTIFICATION(),
        ][$this->value] ?? null;
    }
}
