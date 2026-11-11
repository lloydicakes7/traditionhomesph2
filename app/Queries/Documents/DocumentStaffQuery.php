<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\HoaMember;
use App\Utility\DocumentText;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\Storage;

/**
 * The officers, committees and signatories every document prints.
 *
 * The single source is the `hoa_members` table, in the order the office keeps
 * it: the framed letters read the same sidebar and the same signer, so a change
 * of officer is made once and appears on every sheet. Configuration stands in
 * only while the table has not been filled in yet, so a fresh installation
 * still prints the association's officers rather than an empty sidebar.
 *
 * Nothing here is a view's concern: a document asks for the staff and prints
 * what it is given, and a missing entry prints its blank space rather than
 * disappearing.
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
        $members = HoaMember::query()->orderBy('id')->get();

        $officers = [];
        $committees = [];
        $presidentPhoto = null;

        foreach ($members as $member) {
            $name = DocumentText::clean($member->name);
            $position = DocumentText::clean($member->position);

            if ($name === '' || $position === '') {
                continue;
            }

            $key = $this->officeKey($position);

            if ($key !== null) {
                $officers[$key] = ['name' => $name, 'role' => self::OFFICES[$key]];

                if ($key === 'president' && $member->profile_photo_path) {
                    $presidentPhoto = $this->photo($member->profile_photo_path);
                }

                continue;
            }

            if (stripos($position, 'committee') !== false) {
                $committees[] = ['name' => $name, 'role' => $position];
            }
        }

        if ($officers === [] && $committees === []) {
            [$officers, $committees] = $this->fromConfiguration();
        }

        return [
            'president'  => $officers['president'] ?? $this->blankOfficer('President'),
            'vice'       => $officers['vice-president'] ?? $this->blankOfficer('Vice-President'),
            'secretary'  => $officers['secretary'] ?? $this->blankOfficer('Secretary'),
            'treasurer'  => $officers['treasurer'] ?? $this->blankOfficer('Treasurer'),
            'auditor'    => $officers['auditor'] ?? $this->blankOfficer('Auditor'),
            'committees' => $committees,
            'photo'      => $presidentPhoto,
            'signer'     => $officers['president'] ?? ['name' => '', 'role' => 'THHA-President'],
            'checkers'   => $this->checkers($committees),
            'grievance'  => $this->committee($committees, 'grievance'),
            'recipient'  => $this->recipient(),
        ];
    }

    /**
     * The chair of one committee, as the statements sign it.
     *
     * A statement is sworn before the committee that hears the complaint, so
     * its chair signs beside the president. While the board has no such seat
     * filled the name prints as a blank signature, never a guess.
     *
     * @param  array<int, array{name: string, role: string}>  $committees
     * @return array{name: string, role: string}
     */
    private function committee(array $committees, string $needle): array
    {
        foreach ($committees as $committee) {
            if (stripos($committee['role'], $needle) !== false) {
                return $committee;
            }
        }

        return ['name' => '', 'role' => ucfirst($needle)];
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
     * The two members who check the membership form.
     *
     * The office names them in the print settings; while it has not, the two
     * chairs of the education and membership committee are read from the board,
     * which is where the printed paper takes them from too.
     *
     * @param  array<int, array{name: string, role: string}>  $committees
     * @return array<int, array{name: string, role: string}>
     */
    private function checkers(array $committees): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        $names = [
            DocumentText::clean($settings['membership_checker_1'] ?? ''),
            DocumentText::clean($settings['membership_checker_2'] ?? ''),
        ];

        if ($names[0] === '' && $names[1] === '') {
            foreach ($committees as $committee) {
                if (stripos($committee['role'], 'education and membership') === false) {
                    continue;
                }

                $parts = preg_split('/\s*(?:&|and)\s*/i', $committee['name']) ?: [];
                $names = array_slice(array_map([DocumentText::class, 'clean'], $parts), 0, 2);
                break;
            }
        }

        return [
            ['name' => $names[0] ?? '', 'role' => 'Chairwoman'],
            ['name' => $names[1] ?? '', 'role' => 'Education and Membership Committee'],
        ];
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
     * The president's photo on the public disk, as DomPDF reads it, or null.
     */
    private function photo(string $path): ?string
    {
        $disk = Storage::disk('public');

        return $disk->exists($path) ? $disk->path($path) : null;
    }

    /**
     * The officers and board kept in configuration, used only until the office
     * has entered its members.
     *
     * @return array{0: array<string, array{name: string, role: string}>, 1: array<int, array{name: string, role: string}>}
     */
    private function fromConfiguration(): array
    {
        $officers = [];
        $committees = [];

        foreach ((array) config('thha.officers', []) as $officer) {
            $key = $this->officeKey(DocumentText::clean($officer['position'] ?? ''));

            if ($key !== null) {
                $officers[$key] = [
                    'name' => DocumentText::clean($officer['name'] ?? ''),
                    'role' => self::OFFICES[$key],
                ];
            }
        }

        foreach ((array) config('thha.board', []) as $member) {
            $role = DocumentText::clean($member['position'] ?? '');

            if ($role !== '') {
                $committees[] = ['name' => DocumentText::clean($member['name'] ?? ''), 'role' => $role];
            }
        }

        return [$officers, $committees];
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
