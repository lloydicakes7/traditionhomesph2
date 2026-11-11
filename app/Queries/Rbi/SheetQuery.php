<?php

namespace App\Queries\Rbi;

use App\Queries\Settings\PrintSettingsQuery;

/**
 * The page an RBI sheet is drawn on.
 *
 * The four forms differ in what they say, not in how they are presented: each
 * is printed on the association's letterhead, in the association's own
 * stylesheet, and signed by the same two officers. That chrome lives here so
 * the controllers only hand over the form's data.
 */
class SheetQuery
{
    private PrintSettingsQuery $printSettings;

    public function __construct(PrintSettingsQuery $printSettings)
    {
        $this->printSettings = $printSettings;
    }

    /**
     * What the sheet view and the PDF builder receive.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function payload(string $title, array $data): array
    {
        return [
            'title' => $title,
            'css' => $this->css(),
            'letterhead' => $this->printSettings->letterhead(),
            'signatories' => [
                'secretary' => $this->officer('Secretary'),
                'president' => $this->officer('President'),
            ],
            'data' => $data,
        ];
    }

    /**
     * An officer's name from the association's own list, blank when absent.
     */
    private function officer(string $position): string
    {
        foreach ((array) config('thha.officers', []) as $officer) {
            if (isset($officer['position'], $officer['name'])
                && strcasecmp($officer['position'], $position) === 0) {
                return (string) $officer['name'];
            }
        }

        return '';
    }

    /**
     * The stylesheet the sheets are drawn with, inlined so the PDF never has to
     * resolve a path of its own.
     */
    private function css(): string
    {
        $path = public_path('css/rbi.css');

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
