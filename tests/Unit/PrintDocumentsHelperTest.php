<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Property;
use App\Models\Resident;
use App\Utility\PrintDocumentsHelper;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * The formatting the printed documents share.
 *
 * These are the rules the paper forms are written in — how a name, an address,
 * a span of days and a part of the day read on the page — so they are asserted
 * directly rather than through a document, where a change would show up as a
 * difference on a PDF nobody reads line by line.
 */
class PrintDocumentsHelperTest extends TestCase
{
    /** @var string */
    private const COMMUNITY = 'Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('thha.address', self::COMMUNITY);
    }

    // =====================================================================
    // Names
    // =====================================================================

    public function test_a_name_carries_the_middle_initial_and_the_suffix(): void
    {
        $resident = $this->resident([
            'first_name'  => 'Juan',
            'middle_name' => 'Santos',
            'surname'     => 'Dela Cruz',
            'suffix'      => 'Jr.',
        ]);

        $this->assertSame('Juan S. Dela Cruz, Jr.', PrintDocumentsHelper::displayName($resident));
        $this->assertSame('JUAN S. DELA CRUZ, JR.', PrintDocumentsHelper::nameUpper($resident));
        $this->assertSame('Juan Santos Dela Cruz, Jr.', PrintDocumentsHelper::fullName($resident));
    }

    public function test_a_name_without_a_middle_name_or_suffix_is_still_a_name(): void
    {
        $resident = $this->resident([
            'first_name'  => 'Anna Marie',
            'middle_name' => null,
            'surname'     => 'Lim',
        ]);

        $this->assertSame('Anna Marie Lim', PrintDocumentsHelper::displayName($resident));
        $this->assertSame('Anna Marie Lim', PrintDocumentsHelper::fullName($resident));
    }

    public function test_a_blank_form_has_no_name_at_all(): void
    {
        $this->assertSame('', PrintDocumentsHelper::displayName(null));
        $this->assertSame('', PrintDocumentsHelper::fullName(null));
        $this->assertSame('', PrintDocumentsHelper::nameUpper(null));
    }

    // =====================================================================
    // Addresses
    // =====================================================================

    public function test_the_unit_address_is_the_block_the_lot_and_the_community(): void
    {
        $property = $this->property(['block' => 4, 'lot' => 12]);

        $this->assertSame('Block 4 Lot 12, ' . self::COMMUNITY, PrintDocumentsHelper::unitAddress($property));
        $this->assertSame('Blk 4 · Lot 12', PrintDocumentsHelper::blockLot($property));
    }

    public function test_a_missing_part_of_the_unit_leaves_no_dangling_separator(): void
    {
        $this->assertSame(
            'Lot 12, ' . self::COMMUNITY,
            PrintDocumentsHelper::unitAddress($this->property(['block' => null, 'lot' => 12]))
        );

        $this->assertSame(
            'Block 4, ' . self::COMMUNITY,
            PrintDocumentsHelper::unitAddress($this->property(['block' => 4, 'lot' => null]))
        );

        // Nothing on file at all: the community still stands, and a unit with
        // no property prints nothing rather than a stray comma.
        $this->assertSame(self::COMMUNITY, PrintDocumentsHelper::unitAddress($this->property([])));
        $this->assertSame('', PrintDocumentsHelper::unitAddress(null));
        $this->assertSame('', PrintDocumentsHelper::blockLot(null));
    }

    // =====================================================================
    // Dates
    // =====================================================================

    public function test_dates_are_written_the_way_the_forms_write_them(): void
    {
        $this->assertSame('September 30, 2026', PrintDocumentsHelper::date('2026-09-30'));
        $this->assertSame('Sep 30, 2026', PrintDocumentsHelper::shortDate('2026-09-30'));
        $this->assertSame('Sep 25, 2026 9:30 PM', PrintDocumentsHelper::dateTime('2026-09-25 21:30'));
        $this->assertSame('', PrintDocumentsHelper::date(null));
        $this->assertSame('', PrintDocumentsHelper::date(''));
        $this->assertSame('', PrintDocumentsHelper::date('not a date'));
    }

    public function test_a_day_of_the_month_is_written_as_a_sentence_writes_it(): void
    {
        $this->assertSame('1st', PrintDocumentsHelper::ordinalDay('2026-09-01'));
        $this->assertSame('2nd', PrintDocumentsHelper::ordinalDay('2026-09-02'));
        $this->assertSame('3rd', PrintDocumentsHelper::ordinalDay('2026-09-03'));
        $this->assertSame('4th', PrintDocumentsHelper::ordinalDay('2026-09-04'));
        $this->assertSame('11th', PrintDocumentsHelper::ordinalDay('2026-09-11'));
        $this->assertSame('12th', PrintDocumentsHelper::ordinalDay('2026-09-12'));
        $this->assertSame('13th', PrintDocumentsHelper::ordinalDay('2026-09-13'));
        $this->assertSame('21st', PrintDocumentsHelper::ordinalDay('2026-09-21'));
        $this->assertSame('30th', PrintDocumentsHelper::ordinalDay('2026-09-30'));
        $this->assertSame('', PrintDocumentsHelper::ordinalDay(''));
    }

    public function test_a_span_of_days_says_the_year_once(): void
    {
        // The same month.
        $this->assertSame(
            'October 5 to October 19, 2026',
            PrintDocumentsHelper::dateRange('2026-10-05', '2026-10-19')
        );

        // Across two months of one year…
        $this->assertSame(
            'October 28 to November 2, 2026',
            PrintDocumentsHelper::dateRange('2026-10-28', '2026-11-02')
        );

        // …and across two years, where both ends need one.
        $this->assertSame(
            'December 30, 2026 to January 3, 2027',
            PrintDocumentsHelper::dateRange('2026-12-30', '2027-01-03')
        );

        // One day, or no end given: the permit runs for the day it starts.
        $this->assertSame('October 5, 2026', PrintDocumentsHelper::dateRange('2026-10-05'));
        $this->assertSame('October 5, 2026', PrintDocumentsHelper::dateRange('2026-10-05', '2026-10-05'));
        $this->assertSame('October 5, 2026', PrintDocumentsHelper::dateRange('2026-10-05', ''));
        $this->assertSame('', PrintDocumentsHelper::dateRange(null, '2026-10-05'));
    }

    public function test_a_clock_time_is_written_for_a_letter(): void
    {
        $this->assertSame('3:00 PM', PrintDocumentsHelper::time12('15:00'));
        $this->assertSame('9:30 AM', PrintDocumentsHelper::time12('09:30:00'));
        $this->assertSame('12:05 AM', PrintDocumentsHelper::time12('00:05'));
        $this->assertSame('12:05 PM', PrintDocumentsHelper::time12('12:05'));
        $this->assertSame('3:00 PM', PrintDocumentsHelper::time12('3:00 PM'));

        // A time the salaysay states drops the meridiem — the part of the day
        // that follows it in the sentence is the meridiem.
        $this->assertSame('9:30', PrintDocumentsHelper::clockTime('09:30:00'));
        $this->assertSame('9:30', PrintDocumentsHelper::clockTime('9:30 PM'));
        $this->assertSame('12:05', PrintDocumentsHelper::clockTime('00:05'));

        // A time nobody can read prints itself rather than a wrong hour.
        $this->assertSame('sometime', PrintDocumentsHelper::time12('sometime'));
        $this->assertSame('sometime', PrintDocumentsHelper::clockTime('sometime'));
    }

    public function test_the_part_of_the_day_follows_the_hour(): void
    {
        $this->assertSame('morning', PrintDocumentsHelper::dayPart('00:00'));
        $this->assertSame('morning', PrintDocumentsHelper::dayPart('11:59'));
        $this->assertSame('afternoon', PrintDocumentsHelper::dayPart('12:00'));
        $this->assertSame('afternoon', PrintDocumentsHelper::dayPart('17:59'));
        $this->assertSame('evening', PrintDocumentsHelper::dayPart('18:00'));
        $this->assertSame('evening', PrintDocumentsHelper::dayPart('23:59'));

        // An unreadable time says all three, never one of them.
        $this->assertSame('morning/afternoon/evening', PrintDocumentsHelper::dayPart(''));
        $this->assertSame('morning/afternoon/evening', PrintDocumentsHelper::dayPart(null));
    }

    public function test_the_tagalog_day_part_follows_the_hour(): void
    {
        $this->assertSame('gabi', PrintDocumentsHelper::tagalogDayPart('04:59'));
        $this->assertSame('umaga', PrintDocumentsHelper::tagalogDayPart('05:00'));
        $this->assertSame('umaga', PrintDocumentsHelper::tagalogDayPart('10:59'));
        $this->assertSame('tanghali', PrintDocumentsHelper::tagalogDayPart('11:00'));
        $this->assertSame('tanghali', PrintDocumentsHelper::tagalogDayPart('12:59'));
        $this->assertSame('hapon', PrintDocumentsHelper::tagalogDayPart('13:00'));
        $this->assertSame('hapon', PrintDocumentsHelper::tagalogDayPart('17:59'));
        $this->assertSame('gabi', PrintDocumentsHelper::tagalogDayPart('18:00'));
        $this->assertSame('gabi', PrintDocumentsHelper::tagalogDayPart('21:30'));

        $this->assertSame('umaga/gabi/tanghali/hapon', PrintDocumentsHelper::tagalogDayPart('hapon na'));
    }

    public function test_a_date_reads_in_filipino_for_the_salaysay(): void
    {
        $this->assertSame('Setyembre', PrintDocumentsHelper::tagalogMonth('2026-09-30'));
        $this->assertSame('Mayo', PrintDocumentsHelper::tagalogMonth('2026-05-01'));
        $this->assertSame('30 ng Setyembre 2026', PrintDocumentsHelper::tagalogDate('2026-09-30'));
        $this->assertSame('', PrintDocumentsHelper::tagalogDate(null));
        $this->assertSame('', PrintDocumentsHelper::tagalogMonth(''));
    }

    // =====================================================================
    // Words
    // =====================================================================

    public function test_a_civil_status_is_said_in_filipino(): void
    {
        $this->assertSame('binata', PrintDocumentsHelper::civilStatusPhrase($this->resident(['sex' => 'male', 'civil_status' => 'single'])));
        $this->assertSame('dalaga', PrintDocumentsHelper::civilStatusPhrase($this->resident(['sex' => 'female', 'civil_status' => 'single'])));
        $this->assertSame('may-asawa', PrintDocumentsHelper::civilStatusPhrase($this->resident(['sex' => 'female', 'civil_status' => 'married'])));
        $this->assertSame('biyudo', PrintDocumentsHelper::civilStatusPhrase($this->resident(['sex' => 'male', 'civil_status' => 'widowed'])));
        $this->assertSame('biyuda', PrintDocumentsHelper::civilStatusPhrase($this->resident(['sex' => 'female', 'civil_status' => 'widowed'])));
        $this->assertSame('hiwalay', PrintDocumentsHelper::civilStatusPhrase($this->resident(['civil_status' => 'separated'])));

        // A record that says nothing prints every word it might have been.
        $this->assertSame(
            'may-asawa/binata/dalaga, biyuda',
            PrintDocumentsHelper::civilStatusPhrase($this->resident(['civil_status' => null]))
        );
    }

    public function test_a_noun_takes_the_article_that_fits_it(): void
    {
        $this->assertSame('a meeting', PrintDocumentsHelper::withArticle('meeting'));
        $this->assertSame('an orientation', PrintDocumentsHelper::withArticle('orientation'));
        $this->assertSame('an assembly', PrintDocumentsHelper::withArticle('assembly'));
        $this->assertSame('', PrintDocumentsHelper::withArticle(''));
    }

    public function test_paragraphs_are_split_and_wrapped_onto_ruled_lines(): void
    {
        $this->assertSame(['one', 'two'], PrintDocumentsHelper::paragraphs("one\n\n  two  "));
        $this->assertSame([], PrintDocumentsHelper::paragraphs('   '));
        $this->assertSame([], PrintDocumentsHelper::paragraphs(null));

        $lines = PrintDocumentsHelper::wrapLines(['alpha beta gamma'], 10);

        $this->assertSame(['alpha beta', 'gamma'], $lines);

        // A blank line separates one paragraph from the next.
        $lines = PrintDocumentsHelper::wrapLines(['first', 'second'], 40);

        $this->assertSame(['first', '', 'second'], $lines);

        $this->assertSame([], PrintDocumentsHelper::wrapLines([]));
    }

    // =====================================================================
    // Checkbox groups
    // =====================================================================

    public function test_a_checkbox_group_ticks_the_chosen_option(): void
    {
        $options = PrintDocumentsHelper::options(
            ['owner' => 'Owner', 'tenant' => 'Tenant'],
            'tenant'
        );

        $this->assertSame([
            ['label' => 'Owner', 'checked' => false, 'free' => false, 'text' => '', 'suffix' => ''],
            ['label' => 'Tenant', 'checked' => true, 'free' => false, 'text' => '', 'suffix' => ''],
        ], $options);
    }

    public function test_the_free_option_carries_what_was_written_on_its_line(): void
    {
        $options = PrintDocumentsHelper::options(
            ['business_permit' => 'Business Permit', 'other' => 'Other'],
            'other',
            'other',
            'Water delivery'
        );

        $this->assertFalse($options[0]['free']);
        $this->assertTrue($options[1]['free']);
        $this->assertTrue($options[1]['checked']);
        $this->assertSame('Water delivery', $options[1]['text']);
    }

    public function test_an_option_can_carry_a_suffix_of_its_own(): void
    {
        $options = PrintDocumentsHelper::options([
            'labor_force'   => 'Labor Force / Employed',
            'senior_citizen' => ['label' => 'Senior Citizen', 'suffix' => ' (Registered? Yes / No)'],
        ]);

        $this->assertSame('', $options[0]['suffix']);
        $this->assertSame(' (Registered? Yes / No)', $options[1]['suffix']);
    }

    public function test_an_enum_builds_its_own_checkbox_group(): void
    {
        $options = PrintDocumentsHelper::optionsFromEnum(
            \App\Enums\BusinessPermitPurpose::cases(),
            \App\Enums\BusinessPermitPurpose::OTHER,
            'Karaoke rental'
        );

        $this->assertSame('Business Permit', $options[0]['label']);
        $this->assertFalse($options[0]['free']);

        $last = end($options);

        $this->assertSame('Other', $last['label']);
        $this->assertTrue($last['free']);
        $this->assertTrue($last['checked']);
        $this->assertSame('Karaoke rental', $last['text']);
    }

    // =====================================================================
    // Values
    // =====================================================================

    public function test_a_blank_value_prints_the_fill_in_line(): void
    {
        $this->assertSame('_______________', PrintDocumentsHelper::blank(null));
        $this->assertSame('_______________', PrintDocumentsHelper::blank('   '));
        $this->assertSame('RBI-2026-00001', PrintDocumentsHelper::blank('RBI-2026-00001'));
    }

    public function test_an_amount_is_written_the_way_the_forms_write_it(): void
    {
        $this->assertSame('PHP 50,000.00', PrintDocumentsHelper::money(50000));
        $this->assertSame('PHP 1,500.00', PrintDocumentsHelper::money('1500'));
        $this->assertSame('PHP 0.00', PrintDocumentsHelper::money(null));
        $this->assertSame('—', PrintDocumentsHelper::money(0, true));
    }

    public function test_a_record_column_is_read_by_its_first_name_that_exists(): void
    {
        $resident = $this->resident(['occupation' => 'Teacher', 'religion' => '']);

        // The first of the names the record uses…
        $this->assertSame('Teacher', PrintDocumentsHelper::attribute($resident, ['occupation', 'job']));

        // …an empty one falls through to the next…
        $this->assertSame('Teacher', PrintDocumentsHelper::attribute($resident, ['religion', 'occupation']));

        // …and a column the record does not carry is not an error.
        $this->assertSame('', PrintDocumentsHelper::attribute($resident, ['philsys_no', 'philsys']));
        $this->assertSame('', PrintDocumentsHelper::attribute(null, ['philsys_no']));
    }

    public function test_a_tick_box_column_answers_yes_or_no(): void
    {
        $address = (new Address())->setRawAttributes(['is_primary' => 1]);

        $this->assertSame('Yes', PrintDocumentsHelper::attribute($address, ['is_primary']));

        $address->setRawAttributes(['is_primary' => 0]);

        $this->assertSame('No', PrintDocumentsHelper::attribute($address, ['is_primary']));
    }

    public function test_a_flag_reads_the_way_a_form_sends_it(): void
    {
        $this->assertTrue(PrintDocumentsHelper::flag(true));
        $this->assertTrue(PrintDocumentsHelper::flag('1'));
        $this->assertTrue(PrintDocumentsHelper::flag('on'));
        $this->assertTrue(PrintDocumentsHelper::flag('yes'));
        $this->assertFalse(PrintDocumentsHelper::flag('0'));
        $this->assertFalse(PrintDocumentsHelper::flag(''));
        $this->assertFalse(PrintDocumentsHelper::flag(null));
    }

    public function test_a_suggested_number_is_the_shapes_year_first_number(): void
    {
        $this->assertSame('RBI-' . date('Y') . '-00001', PrintDocumentsHelper::suggestedNumber('RBI', 5));
        $this->assertSame('BP-' . date('Y') . '-0001', PrintDocumentsHelper::suggestedNumber('BP'));
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resident(array $attributes): Resident
    {
        return new Resident($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function property(array $attributes): Property
    {
        return new Property($attributes);
    }
}
