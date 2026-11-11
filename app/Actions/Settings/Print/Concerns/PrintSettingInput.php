<?php

namespace App\Actions\Settings\Print\Concerns;

use App\Utility\HtmlHelper;
use App\Utility\PrintDocumentsHelper;

trait PrintSettingInput
{
    // ===== The letterhead's own keys =====

    private function group(): string
    {
        return 'print';
    }

    /**
     * @return array<int, string>
     */
    private function keys(): array
    {
        return [
            'header_html',
            'left_image_path',
            'right_image_path',
            'left_image_width',
            'right_image_width',
            'left_show',
            'right_show',
            'divider',
            'footer_text',
            'footer_image_path',
            'footer_image_width',
            'footer_show',
            'paper',
            'president_name',
            'secretary_name',
            'treasurer_name',
            'grievance_name',
            'membership_checker_1',
            'membership_checker_2',
        ];
    }

    /**
     * The image slot a side of the letterhead keeps its file in.
     */
    private function imageKey(string $side): string
    {
        $keys = [
            'left'   => 'left_image_path',
            'right'  => 'right_image_path',
            'footer' => 'footer_image_path',
        ];

        if (! array_key_exists($side, $keys)) {
            throw new \InvalidArgumentException('Unknown print image slot: ' . $side);
        }

        return $keys[$side];
    }

    /**
     * The form's input as the settings rows it is stored in.
     *
     * Only the keys the letterhead owns are written, the two blocks of text are
     * sanitized, and a switch is stored as the `1`/`0` the reader expects. The
     * widths and the paper are kept as given: the reader clamps them, and the
     * form has already validated them.
     *
     * @return array<string, string>
     */
    private function storePayload(array $data): array
    {
        $switches = ['left_show', 'right_show', 'divider', 'footer_show'];
        $stored = [];

        foreach ($this->keys() as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if ($key === 'header_html' || $key === 'footer_text') {
                $value = HtmlHelper::sanitize((string) $value);
            }

            if (in_array($key, $switches, true)) {
                $value = PrintDocumentsHelper::flag($value) ? '1' : '0';
            }

            $stored[$key] = (string) $value;
        }

        return $stored;
    }
}
