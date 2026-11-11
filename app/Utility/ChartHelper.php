<?php

namespace App\Utility;

/**
 * How the Reports suite paints a figure.
 *
 * A chart is drawn with the association's own theme tokens rather than a
 * charting library's colours, so a slice of a pie and a bar in a bar chart read
 * as part of the panel they sit in. The palette is a fixed, ordered set — the
 * AdminLTE themes the theme overlay tokenizes, base first and deep after — so a
 * distribution with more categories than themes simply cycles rather than
 * inventing a colour, and two charts that use the same order never paint the
 * same category differently.
 *
 * The class knows nothing about a report or a model: it turns a theme name into
 * the CSS colour expression a chart component writes into a style attribute.
 */
class ChartHelper
{
    /**
     * The themes a chart may be painted in, in the order a series takes them.
     *
     * Base themes first, their deep variants after, because a pie of a dozen
     * religions wants its neighbouring slices to differ and the deep token of a
     * hue is its own distinctly darker colour.
     *
     * @var string[]
     */
    const PALETTE = [
        'primary',
        'info',
        'success',
        'warning',
        'danger',
        'secondary',
        'primary-deep',
        'info-deep',
        'success-deep',
        'warning-deep',
        'danger-deep',
        'secondary-deep',
    ];

    /**
     * The CSS colour expression for an AdminLTE theme.
     *
     * An unrecognised theme falls back to the association's primary rather than
     * emitting an undefined variable that would paint transparent.
     */
    public static function color(?string $theme): string
    {
        $theme = in_array($theme, self::PALETTE, true) ? $theme : 'primary';

        return 'var(--' . $theme . ')';
    }

    /**
     * The palette, cycling from the start — the colour the nth series takes
     * when it names no theme of its own.
     */
    public static function colorForIndex(int $index): string
    {
        $palette = self::PALETTE;

        return self::color($palette[$index % count($palette)]);
    }
}
