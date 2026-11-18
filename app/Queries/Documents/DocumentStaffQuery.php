<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\HoaMember;
use App\Models\HoaMemberGroup;
use App\Utility\DocumentText;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;

/**
 * The officers, board and signatories every document prints.
 *
 * The staff sidebar is read from the `hoa_members` table, and the board from
 * the members assigned to the Board of Directors group. The names under the
 * signature lines, and the presidential portrait, are the print settings: the
 * one place the office changes who signs, beside the letterhead those sheets
 * are drawn on.
 */
class DocumentStaffQuery
{
    const GROUP = 'print';

    /** The positions that sit above the board in the sidebar. */
    const OFFICES = [
        'president'      => 'President',
        'vice-president' => 'Vice-President',
        'secretary'      => 'Secretary',
        'treasurer'      => 'Treasurer',
        'auditor'        => 'Auditor',
    ];

    /**
     * The staff a document prints.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $members = HoaMember::query()->with('group')->orderBy('id')->get();

        $officers = [];
        $board = [];

        foreach ($members as $member) {
            $name = DocumentText::clean($member->name);
            $position = DocumentText::clean($member->position);

            if ($name === '' || $position === '') {
                continue;
            }

            if ($this->isBoardMember($member)) {
                $board[] = ['name' => $name, 'role' => $position];

                continue;
            }

            $key = $this->officeKey($position);

            if ($key !== null) {
                $officers[$key] = ['name' => $name, 'role' => self::OFFICES[$key]];
            }
        }

        if ($officers === []) {
            $officers = $this->fromConfiguration();
        }

        $signatories = $this->signatories();

        return [
            'president'   => $officers['president'] ?? $this->blankOfficer('President'),
            'vice'        => $officers['vice-president'] ?? $this->blankOfficer('Vice-President'),
            'secretary'   => $officers['secretary'] ?? $this->blankOfficer('Secretary'),
            'treasurer'   => $officers['treasurer'] ?? $this->blankOfficer('Treasurer'),
            'auditor'     => $officers['auditor'] ?? $this->blankOfficer('Auditor'),
            'board'       => $board,
            'photo'       => $this->presidentImage(),
            'signer'      => ['name' => $signatories['president'], 'role' => 'THHA-President'],
            'checkers'    => [
                ['name' => $signatories['membership_checker_1'], 'role' => 'Chairwoman'],
                ['name' => $signatories['membership_checker_2'], 'role' => 'Education and Membership Committee'],
            ],
            'grievance'   => ['name' => $signatories['grievance'], 'role' => 'Grievance Committee'],
            'signatories' => $signatories,
            'recipient'   => $this->recipient(),
        ];
    }

    /**
     * The barangay official an endorsement letter is addressed to.
     *
     * @return array<string, string>
     */
    public function recipient(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);
        $config = (array) config('documents.recipient', []);

        return [
            'name'     => DocumentText::clean($settings['barangay_chairperson'] ?? '') ?: (string) ($config['name'] ?? ''),
            'position' => (string) ($config['position'] ?? ''),
            'office'   => (string) ($config['office'] ?? ''),
            'city'     => (string) ($config['city'] ?? ''),
        ];
    }

    /**
     * The names the office set on the print settings, as every signature line
     * reads them.
     *
     * @return array<string, string>
     */
    private function signatories(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            'president'            => DocumentText::clean($settings['president_name'] ?? ''),
            'secretary'            => DocumentText::clean($settings['secretary_name'] ?? ''),
            'treasurer'            => DocumentText::clean($settings['treasurer_name'] ?? ''),
            'grievance'            => DocumentText::clean($settings['grievance_name'] ?? ''),
            'membership_checker_1' => DocumentText::clean($settings['membership_checker_1'] ?? ''),
            'membership_checker_2' => DocumentText::clean($settings['membership_checker_2'] ?? ''),
        ];
    }

    /**
     * The presidential portrait uploaded on the print settings, as DomPDF reads
     * it, or null while the office has not uploaded one.
     */
    private function presidentImage(): ?string
    {
        return StorageHelper::privatePdfImage(
            (string) SettingsHelper::get('president_image_path', self::GROUP, '')
        );
    }

    /**
     * Whether a member belongs to the Board of Directors group.
     */
    private function isBoardMember(HoaMember $member): bool
    {
        return $member->group !== null
            && strcasecmp($member->group->name, HoaMemberGroup::BOARD) === 0;
    }

    /**
     * The office a position belongs to, or null for a committee seat.
     */
    private function officeKey(string $position): ?string
    {
        $normalised = strtolower(preg_replace('/\s+/', '-', trim($position)) ?? '');

        foreach (array_keys(self::OFFICES) as $key) {
            if ($normalised === $key || strpos($normalised, $key) === 0) {
                return $key;
            }
        }

        return null;
    }

    /**
     * The officers kept in configuration, used only until the office has
     * entered its members.
     *
     * @return array<string, array{name: string, role: string}>
     */
    private function fromConfiguration(): array
    {
        $officers = [];

        foreach ((array) config('thha.officers', []) as $officer) {
            $key = $this->officeKey(DocumentText::clean($officer['position'] ?? ''));

            if ($key !== null) {
                $officers[$key] = [
                    'name' => DocumentText::clean($officer['name'] ?? ''),
                    'role' => self::OFFICES[$key],
                ];
            }
        }

        return $officers;
    }

    /**
     * An office with no name yet: its role still prints.
     *
     * @return array{name: string, role: string}
     */
    private function blankOfficer(string $role): array
    {
        return ['name' => '', 'role' => $role];
    }
}
