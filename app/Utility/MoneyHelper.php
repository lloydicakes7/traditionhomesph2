<?php

namespace App\Utility;

/**
 * Formatting of money amounts, the way the association writes them.
 *
 * Technical only: it knows how to write an amount, never what the amount is
 * for. The currency symbol is configuration (`config/thha.php`), so no view
 * and no model spells it out.
 */
class MoneyHelper
{
    const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    const SCALES = ['', 'Thousand', 'Million', 'Billion'];

    /**
     * The currency amount are written in.
     */
    public static function symbol(): string
    {
        return (string) config('thha.currency_symbol', '₱');
    }

    /**
     * An amount as a record writes it: "₱1,500.00".
     *
     * @param  float|string|null  $amount
     * @param  int                $decimals
     * @return string
     */
    public static function format($amount, int $decimals = 2): string
    {
        return self::symbol() . number_format((float) $amount, $decimals);
    }

    /**
     * An amount the short way: "₱1,500" for a whole amount, "₱120.50" when
     * the cents matter. Used on cards, where the rate is a headline rather
     * than a figure to reconcile.
     *
     * @param  float|string|null  $amount
     * @return string
     */
    public static function formatShort($amount): string
    {
        $amount = (float) $amount;

        return self::format($amount, floor($amount) === $amount ? 0 : 2);
    }

    /**
     * An amount written out, the way a receipt spells it:
     * "One Thousand Five Hundred Pesos and 50/100 Only".
     */
    public static function toWords(string $amount): string
    {
        $normalized = bcadd($amount === '' ? '0' : $amount, '0', 2);

        [$whole, $cents] = array_pad(explode('.', $normalized), 2, '0');

        $words = self::numberToWords(abs((int) $whole)) . ' ' . ((int) $whole === 1 ? 'Peso' : 'Pesos');

        if ((int) $cents > 0) {
            $words .= ' and ' . $cents . '/100';
        }

        return $words . ' Only';
    }

    /**
     * A whole number in English, in groups of three: "One Million Two Hundred".
     */
    private static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $groups = [];

        foreach (self::SCALES as $scale) {
            if ($number === 0) {
                break;
            }

            $chunk = $number % 1000;
            $number = intdiv($number, 1000);

            if ($chunk === 0) {
                continue;
            }

            $words = self::chunkToWords($chunk);

            $groups[] = $scale === '' ? $words : $words . ' ' . $scale;
        }

        return implode(' ', array_reverse($groups));
    }

    /**
     * The words for one group of three digits: "Nine Hundred Ninety Nine".
     */
    private static function chunkToWords(int $chunk): string
    {
        $words = [];

        if ($chunk >= 100) {
            $words[] = self::ONES[intdiv($chunk, 100)] . ' Hundred';
            $chunk %= 100;
        }

        if ($chunk >= 20) {
            $words[] = self::TENS[intdiv($chunk, 10)];
            $chunk %= 10;
        }

        if ($chunk > 0) {
            $words[] = self::ONES[$chunk];
        }

        return implode(' ', $words);
    }
}
