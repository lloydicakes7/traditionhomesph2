<?php

namespace App\Queries\Settings;

use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;

/**
 * The association's letterhead and footer, as every printed page reads them.
 *
 * The office sets the header, the images, the widths, the paper and the names
 * that sign; this turns the stored strings into the values the print settings
 * form edits and the values the documents draw. Coercion happens here, on the
 * way out, so a value saved by an older version of the form can never print
 * outside the range the paper allows.
 */
class PrintSettingsQuery
{
    const GROUP = 'print';

    const DEFAULT_WIDTH = 24;

    const MIN_WIDTH = 10;

    const MAX_WIDTH = 45;

    const DEFAULT_FOOTER_WIDTH = 30;

    const MIN_FOOTER_WIDTH = 10;

    const MAX_FOOTER_WIDTH = 80;

    const PAPERS = ['a4', 'letter', 'legal'];



    /**
     * The system images a slot falls back to until the office uploads its own:
     * the association's logo on the left and the Hope Trece mark on the right,
     * both shipped with the system.
     *
     * @var array<string, string>
     */
    const SEEDED_IMAGES = [
        'left'   => 'documents.assets.seal',
        'right'  => 'documents.assets.hope',
        'footer' => 'documents.assets.bagong',
    ];

    const WIDTH_KEYS = [
        'left_image_width'   => [self::MIN_WIDTH, self::MAX_WIDTH],
        'right_image_width'  => [self::MIN_WIDTH, self::MAX_WIDTH],
        'footer_image_width' => [self::MIN_FOOTER_WIDTH, self::MAX_FOOTER_WIDTH],
    ];


    private GeneralSettingsQuery $general;

    public function __construct(GeneralSettingsQuery $general)
    {
        $this->general = $general;
    }

    // ===== Images =====

    public static function imageMimes(): string
    {
        return StorageHelper::supportsImageDecoding()
            ? 'png,jpg,jpeg,gif,webp'
            : 'jpg,jpeg';
    }

    public static function imageAccept(): string
    {
        return '.' . str_replace(',', ',.', self::imageMimes());
    }

    public static function imageFormatsLabel(): string
    {
        $labels = array_map('strtoupper', explode(',', self::imageMimes()));

        $last = array_pop($labels);

        return $labels ? implode(', ', $labels) . ' or ' . $last : $last;
    }

    /**
     * A slot's system image, as the absolute path DomPDF reads, or null while
     * the file is not on the server.
     */
    public static function seededImage(string $side): ?string
    {
        $relative = (string) config(self::SEEDED_IMAGES[$side] ?? '', '');
        $path = $relative !== '' ? public_path($relative) : '';

        return ($path !== '' && is_file($path)) ? $path : null;
    }

    /**
     * A slot's system image as a URL the settings page can preview.
     */
    public static function seededImageUrl(string $side): ?string
    {
        $relative = (string) config(self::SEEDED_IMAGES[$side] ?? '', '');

        return ($relative !== '' && self::seededImage($side) !== null) ? asset($relative) : null;
    }

    // ===== Read =====

    public function load(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            'header_html'          => (string) ($settings['header_html'] ?? ''),
            'left_image_path'      => (string) ($settings['left_image_path'] ?? ''),
            'right_image_path'     => (string) ($settings['right_image_path'] ?? ''),
            'left_image_width'     => $this->width($settings['left_image_width'] ?? null),
            'right_image_width'    => $this->width($settings['right_image_width'] ?? null),
            'left_show'            => $this->flag($settings['left_show'] ?? null, true),
            'right_show'           => $this->flag($settings['right_show'] ?? null, true),
            'divider'              => $this->flag($settings['divider'] ?? null, true),
            'footer_text'          => (string) ($settings['footer_text'] ?? ''),
            'footer_image_path'    => (string) ($settings['footer_image_path'] ?? ''),
            'footer_image_width'   => $this->footerWidth($settings['footer_image_width'] ?? null),
            'footer_show'          => $this->flag($settings['footer_show'] ?? null, true),
            'paper'                => $this->paper($settings['paper'] ?? null),
            'president_name'       => (string) ($settings['president_name'] ?? ''),
            'secretary_name'       => (string) ($settings['secretary_name'] ?? ''),
            'treasurer_name'       => (string) ($settings['treasurer_name'] ?? ''),
            'grievance_name'       => (string) ($settings['grievance_name'] ?? ''),
            'membership_checker_1' => (string) ($settings['membership_checker_1'] ?? ''),
            'membership_checker_2' => (string) ($settings['membership_checker_2'] ?? ''),
        ];
    }

    public function letterhead(): array
    {
        $settings = $this->load();

        $general = $this->general->load();

        return [
            'hoa_name'     => $general['hoa_name'] ?: config('thha.hoa_name', 'THHA'),
            'hoa_abbr'     => $this->hoaAbbr($general),
            'header_html'  => $settings['header_html'] !== ''
                ? $settings['header_html']
                : $this->defaultHeaderHtml(),
            'left_image'   => $settings['left_show']
                ? (StorageHelper::privatePdfImage($settings['left_image_path']) ?: self::seededImage('left'))
                : null,
            'right_image'  => $settings['right_show']
                ? (StorageHelper::privatePdfImage($settings['right_image_path']) ?: self::seededImage('right'))
                : null,
            'left_width'   => $settings['left_image_width'],
            'right_width'  => $settings['right_image_width'],
            'left_show'    => $settings['left_show'],
            'right_show'   => $settings['right_show'],
            'divider'      => $settings['divider'],
            'footer_text'  => $settings['footer_show'] ? $settings['footer_text'] : '',
            'footer_image' => $settings['footer_show']
                ? (StorageHelper::privatePdfImage($settings['footer_image_path']) ?: self::seededImage('footer'))
                : null,
            'footer_width' => $settings['footer_image_width'],
            'footer_show'  => $settings['footer_show'],
            'paper'        => $settings['paper'],
            'signatories'  => [
                'president'            => $settings['president_name'],
                'secretary'            => $settings['secretary_name'],
                'treasurer'            => $settings['treasurer_name'],
                'grievance'            => $settings['grievance_name'],
                'membership_checker_1' => $settings['membership_checker_1'],
                'membership_checker_2' => $settings['membership_checker_2'],
            ],
            'officers'     => config('thha.officers', []),
            'board'        => config('thha.board', []),
        ];
    }

    public function defaultHeaderHtml(): string
    {
        $general = $this->general->load();

        $name = strtoupper($general['hoa_name'] ?: config('thha.hoa_name', 'THHA'));

        $lines = ['<div style="font-size:11pt;font-weight:bold;">' . e($name) . '</div>'];

        if (! empty($general['address'])) {
            $lines[] = '<div style="font-size:8pt;">' . e($general['address']) . '</div>';
        }

        $contact = array_filter([
            $general['email'] ?? null,
            $general['contact_number'] ?? null,
        ]);

        if (! empty($contact)) {
            $lines[] = '<div style="font-size:8pt;">' . e(implode(' · ', $contact)) . '</div>';
        }

        return implode('', $lines);
    }

    // ===== Coercion =====

    public function width($value): float
    {
        $width = is_numeric($value) ? (float) $value : (float) self::DEFAULT_WIDTH;

        return max(self::MIN_WIDTH, min(self::MAX_WIDTH, $width));
    }

    public function footerWidth($value): float
    {
        $width = is_numeric($value) ? (float) $value : (float) self::DEFAULT_FOOTER_WIDTH;

        return max(self::MIN_FOOTER_WIDTH, min(self::MAX_FOOTER_WIDTH, $width));
    }

    public function hoaAbbr(array $general): string
    {
        $abbr = trim((string) ($general['hoa_abbr'] ?? ''));

        if ($abbr === '') {
            $abbr = trim((string) SettingsHelper::get('hoa_abbr', GeneralSettingsQuery::GROUP));
        }

        return $abbr !== '' ? $abbr : (string) config('thha.hoa_abbr', 'THHA');
    }

    public function flag($value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    public function paper($value): string
    {
        $value = strtolower((string) $value);

        return in_array($value, self::PAPERS, true) ? $value : 'a4';
    }


    // ===== Queries =====

    public function form(): array
    {
        return $this->load();
    }

    public function allowedImageFormats(): string
    {
        return self::imageFormatsLabel();
    }
}
