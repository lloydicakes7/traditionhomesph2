<?php

namespace App\Services\Csv;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\SectorType;
use App\Enums\Sex;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Utility\CsvHelper;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Residents CSV import, export and template.
 *
 * A sheet is the template's columns. A row is skipped, with the reason and its
 * line number, when a required value is missing, a value is not the type the
 * column holds, or the record is already saved.
 */
class ResidentCsvService
{
    private const SECTOR_SEPARATOR = ';';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Operations =====

    /**
     * The sheet's columns, in the order it writes them: every editable column
     * of `residents`, plus the account address and the photo path it holds.
     *
     * @return array<int, string>
     */
    public function getColumnMap(): array
    {
        return [
            'philsys_card_no',
            'surname', 'first_name', 'middle_name', 'suffix',
            'birth_date', 'birth_place', 'sex', 'civil_status', 'spouse_name',
            'citizenship', 'religion', 'occupation', 'mobile_no', 'educational_attainment',
            'sectors', 'email', 'profile_photo_path',
        ];
    }

    /**
     * Import a residents sheet.
     *
     * @return array{imported: int, skipped: array<int, string>}
     *
     * @throws \InvalidArgumentException When the file is not the template
     */
    public function import(string $filePath, Admin $by): array
    {
        $handle = CsvHelper::openRead($filePath);
        $columns = $this->getColumnMap();

        try {
            $headerMap = CsvHelper::headerMap($handle, $columns);
            $result = $this->importRows($handle, $headerMap, $columns);
        } finally {
            fclose($handle);
        }

        $this->activity->log(
            $by,
            'imported',
            'homeowner',
            "Imported {$result['imported']} residents from CSV"
            . ($result['skipped'] === [] ? '' : ' (' . count($result['skipped']) . ' skipped)')
        );

        return $result;
    }

    public function export(string $sortField = 'surname', string $sortDirection = 'asc'): string
    {
        $rows = Resident::with('user')
            ->orderBy($sortField, $sortDirection)
            ->get()
            ->map(function (Resident $resident) {
                return $this->exportRow($resident);
            })
            ->all();

        return CsvHelper::build($this->getColumnMap(), $rows);
    }

    public function getImportTemplate(): string
    {
        return CsvHelper::buildHeader($this->getColumnMap());
    }

    // ===== Import =====

    /**
     * @param  resource  $handle
     * @param  array<int, string>  $headerMap
     * @param  array<int, string>  $columns
     * @return array{imported: int, skipped: array<int, string>}
     */
    private function importRows($handle, array $headerMap, array $columns): array
    {
        $imported = 0;
        $skipped = [];
        $seen = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $data = CsvHelper::mapRow($row, $headerMap, $columns);

                if ($data['surname'] === '' && $data['first_name'] === '') {
                    continue;
                }

                $values = $this->rowValues($data);

                if (is_string($values)) {
                    $skipped[] = "Row {$rowNum}: {$values}";
                    continue;
                }

                $problem = $this->duplicate($values, $data['email'], $seen);

                if ($problem !== null) {
                    $skipped[] = "Row {$rowNum}: {$problem}";
                    continue;
                }

                $resident = Resident::create($values);

                $this->linkAccount($resident, $data['email']);

                $seen[$this->residentKey($values)] = true;

                if ($values['philsys_card_no'] !== null) {
                    $seen['philsys:' . $values['philsys_card_no']] = true;
                }

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        if ($imported === 0 && $skipped === []) {
            throw new \InvalidArgumentException(
                'The sheet has no resident rows. Fill in the rows below the header and import it again.'
            );
        }

        return compact('imported', 'skipped');
    }

    /**
     * The resident columns a sheet row writes, or the reason it cannot be read.
     *
     * @return array<string, mixed>|string
     */
    private function rowValues(array $data)
    {
        $error = $this->rowError($data);

        if ($error !== null) {
            return $error;
        }

        $civilStatus = $this->enumValue(CivilStatus::labels(), $data['civil_status']);

        return [
            'philsys_card_no'        => $this->philsysValue($data['philsys_card_no']),
            'surname'                => $data['surname'],
            'first_name'             => $data['first_name'],
            'middle_name'            => $data['middle_name'] ?: null,
            'suffix'                 => $data['suffix'] ?: null,
            'birth_date'             => $this->dateValue($data['birth_date']),
            'birth_place'            => $data['birth_place'] ?: null,
            'sex'                    => $this->enumValue(Sex::labels(), $data['sex']),
            'civil_status'           => $civilStatus,
            'spouse_name'            => CivilStatus::isMarried($civilStatus) && $data['spouse_name'] !== ''
                ? $data['spouse_name']
                : null,
            'citizenship'            => $data['citizenship'],
            'religion'               => $data['religion'] ?: null,
            'occupation'             => $data['occupation'] ?: null,
            'mobile_no'              => $data['mobile_no'] ?: null,
            'educational_attainment' => $this->enumValue(EducationalAttainment::labels(), $data['educational_attainment']),
            'sectors'                => $this->sectorValues($data['sectors']),
            'profile_photo_path'     => $this->photoPath($data['profile_photo_path']),
        ];
    }

    private function rowError(array $data): ?string
    {
        return $this->nameError($data)
            ?? $this->enumError($data)
            ?? $this->detailError($data);
    }

    private function nameError(array $data): ?string
    {
        if ($data['surname'] !== '' && $data['first_name'] !== '') {
            return null;
        }

        return 'surname and first name are both required.';
    }

    private function enumError(array $data): ?string
    {
        $columns = [
            'sex'                    => Sex::labels(),
            'civil_status'           => CivilStatus::labels(),
            'educational_attainment' => EducationalAttainment::labels(),
        ];

        foreach ($columns as $column => $labels) {
            if ($this->enumValue($labels, $data[$column]) === null) {
                return "{$column} is required and must be one of " . implode(', ', array_keys($labels)) . '.';
            }
        }

        return null;
    }

    private function detailError(array $data): ?string
    {
        if ($this->dateValue($data['birth_date']) === null) {
            return 'birth_date is required and must be a real date written as YYYY-MM-DD or DD/MM/YYYY.';
        }

        if ($data['citizenship'] === '') {
            return 'citizenship is required.';
        }

        if ($data['philsys_card_no'] !== '' && $this->philsysValue($data['philsys_card_no']) === null) {
            return 'philsys_card_no must hold 16 digits.';
        }

        if ($data['mobile_no'] !== '' && preg_match('/^[0-9+()\s-]{7,20}$/', $data['mobile_no']) !== 1) {
            return 'mobile_no must be a number of 7 to 20 digits, spaces and the characters + ( ) - only.';
        }

        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return 'email "' . $data['email'] . '" is not a valid email address.';
        }

        return null;
    }

    /**
     * Why a row repeats a record, or null when it is new.
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, bool>  $seen
     */
    private function duplicate(array $values, string $email, array $seen): ?string
    {
        if (isset($seen[$this->residentKey($values)])) {
            return 'this resident is listed earlier in the same file.';
        }

        if ($values['philsys_card_no'] !== null && isset($seen['philsys:' . $values['philsys_card_no']])) {
            return 'this PhilSys card number is listed earlier in the same file.';
        }

        $saved = Resident::whereRaw('LOWER(TRIM(surname)) = ?', [strtolower($values['surname'])])
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower($values['first_name'])])
            ->whereDate('birth_date', $values['birth_date']);

        if ($saved->exists()) {
            return 'a resident with this name and birth date is already saved.';
        }

        if (
            $values['philsys_card_no'] !== null
            && Resident::where('philsys_card_no', $values['philsys_card_no'])->exists()
        ) {
            return 'a resident with this PhilSys card number is already saved.';
        }

        if ($email !== '' && User::where('email', $email)->exists()) {
            return 'the email ' . $email . ' already belongs to another account.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function residentKey(array $values): string
    {
        return strtolower($values['surname'] . '|' . $values['first_name'] . '|' . $values['birth_date']);
    }

    /**
     * Give a row with an email a portal account, the way a profile added by
     * hand gets one.
     */
    private function linkAccount(Resident $resident, string $email): void
    {
        if ($email === '') {
            return;
        }

        User::create([
            'resident_id' => $resident->id,
            'email'       => $email,
            'password'    => Hash::make(Str::random(40)),
            'is_active'   => true,
        ]);
    }

    // ===== Export =====

    private function exportRow(Resident $resident): array
    {
        return [
            $resident->philsys_card_no ?? '',
            $resident->surname ?? '',
            $resident->first_name ?? '',
            $resident->middle_name ?? '',
            $resident->suffix ?? '',
            $resident->birth_date ? $resident->birth_date->format('Y-m-d') : '',
            $resident->birth_place ?? '',
            $this->labelValue(Sex::labels(), $resident->sex),
            $this->labelValue(CivilStatus::labels(), $resident->civil_status),
            $resident->spouse_name ?? '',
            $resident->citizenship ?? '',
            $resident->religion ?? '',
            $resident->occupation ?? '',
            $resident->mobile_no ?? '',
            $this->labelValue(EducationalAttainment::labels(), $resident->educational_attainment),
            $this->sectorLabels($resident),
            $resident->user?->email ?? '',
            $resident->profile_photo_path ?? '',
        ];
    }

    /**
     * A stored enum value as its label, so the sheet reads like the screen.
     */
    private function labelValue(array $labels, $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return $labels[$value] ?? (string) $value;
    }

    private function sectorLabels(Resident $resident): string
    {
        return implode(self::SECTOR_SEPARATOR, array_map(function (string $sector) {
            return SectorType::labels()[$sector] ?? $sector;
        }, $resident->sectorValues()));
    }

    // ===== Helpers =====

    /**
     * The stored value behind a sheet cell, accepting the value or its label.
     */
    private function enumValue(array $labels, string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        foreach ($labels as $value => $label) {
            if (strcasecmp($input, (string) $value) === 0 || strcasecmp($input, $label) === 0) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * The sector values in a cell, written as `;`- or `,`-separated tags.
     *
     * @return array<int, string>
     */
    private function sectorValues(string $input): array
    {
        $values = [];

        foreach (preg_split('/[;,]/', $input) ?: [] as $sector) {
            $value = $this->enumValue(SectorType::labels(), $sector);

            if ($value !== null) {
                $values[] = $value;
            }
        }

        return array_values(array_unique($values));
    }

    private function dateValue(string $input): ?string
    {
        $input = trim($input);

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            $date = DateTime::createFromFormat('!' . $format, $input);

            if ($date !== false && $date->format($format) === $input) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function philsysValue(string $input): ?string
    {
        $digits = preg_replace('/\D/', '', $input);

        return strlen($digits) === 16 ? $digits : null;
    }

    private function photoPath(string $input): ?string
    {
        $path = trim($input);

        return $path !== '' && Storage::disk('public')->exists($path) ? $path : null;
    }
}
