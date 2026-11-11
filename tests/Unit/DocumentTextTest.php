<?php

namespace Tests\Unit;

use App\Utility\DocumentText;
use PHPUnit\Framework\TestCase;

/**
 * The helper every printed value passes through.
 *
 * Its one promise: a value that is null, an array, an object or only whitespace
 * is an empty string, never the word "null" and never a guessed dash.
 */
class DocumentTextTest extends TestCase
{
    public function test_clean_never_prints_a_placeholder(): void
    {
        $this->assertSame('', DocumentText::clean(null));
        $this->assertSame('', DocumentText::clean([]));
        $this->assertSame('', DocumentText::clean(['a']));
        $this->assertSame('', DocumentText::clean(new \stdClass()));
        $this->assertSame('', DocumentText::clean('   '));
        $this->assertSame('0', DocumentText::clean('0'));
        $this->assertSame('Juan Dela Cruz', DocumentText::clean(" Juan \n Dela Cruz "));
    }

    public function test_fit_shortens_a_value_to_its_blank(): void
    {
        $this->assertSame('Juan', DocumentText::fit('Juan', 10));
        $this->assertSame('', DocumentText::fit('', 10));
        $this->assertSame(10, mb_strlen(DocumentText::fit('Maximiliano Dela Cruz', 10)));
        $this->assertSame('A', DocumentText::fit('ABC', 1));
    }

    public function test_date_parts_are_empty_for_a_value_that_is_not_a_date(): void
    {
        $this->assertSame('', DocumentText::dateParts(null)['long']);
        $this->assertSame('', DocumentText::dateParts('not a date')['year']);

        $parts = DocumentText::dateParts('2026-10-05');

        $this->assertSame('5', $parts['day']);
        $this->assertSame('5th', $parts['dayOrdinal']);
        $this->assertSame('October', $parts['month']);
        $this->assertSame('2026', $parts['year']);
        $this->assertSame('6', $parts['yearShort']);
        $this->assertSame('October 5, 2026', $parts['long']);
    }

    public function test_wrap_returns_the_lines_the_text_needs(): void
    {
        $this->assertSame([], DocumentText::wrap('', 20, 3));
        $this->assertSame(['one two three'], DocumentText::wrap('one two three', 40, 3));

        $lines = DocumentText::wrap('alpha beta gamma delta epsilon', 10, 2);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(10, mb_strlen($line));
        }

        $this->assertGreaterThan(2, count($lines), 'wrap must not lose the rest of the text');
    }

    public function test_block_lot_names_a_unit(): void
    {
        $unit = DocumentText::blockLot(4, 12);

        $this->assertSame('4', $unit['block']);
        $this->assertSame('12', $unit['lot']);
        $this->assertSame('Block 4 Lot 12', $unit['label']);
        $this->assertSame('Blk 4 Lot 12', $unit['short']);
        $this->assertSame('', DocumentText::blockLot(null, null)['label']);
    }

    public function test_person_never_leaves_a_stray_comma(): void
    {
        $this->assertSame('Dela Cruz, Juan S.', DocumentText::person('Dela Cruz', 'Juan', 'Santos', null, true));
        $this->assertSame('Dela Cruz, Juan', DocumentText::person('Dela Cruz', 'Juan'));
        $this->assertSame('Dela Cruz', DocumentText::person('Dela Cruz'));
        $this->assertSame('Dela Cruz, Juan, Jr.', DocumentText::person('Dela Cruz', 'Juan', null, 'Jr.'));
        $this->assertSame('', DocumentText::person(null));
        $this->assertSame('Juan Santos', DocumentText::join(['', 'Juan', '   ', 'Santos']));
    }
}
