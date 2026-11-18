<?php

namespace App\Services\Csv;

use App\Actions\Homeowners\Residents\FirstOrCreateResident;
use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\HouseholdType;
use App\Enums\Sex;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Utility\CsvHelper;
use DateTime;
use Illuminate\Support\Facades\DB;

/**
 * Households CSV import, export and template.
 *
 * A row is one member. The household comes from the property ID, and the one
 * row marked as head becomes the family head. A row is skipped, with the reason
 * and its line number, when a required value is missing, a value is not the
 * type the column holds, the property does not exist, or the resident already
 * belongs to a household.
 */
class HouseholdCsvService
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Columns =====

    /**
     * @return array<int, string>
     */
    public function getColumnMap(): array
    {
        return [
            'property', 'type', 'prev_address',
            'is_head', 'relationship',
            'surname', 'first_name', 'middle_name', 'suffix',
            'birth_date', 'birth_place', 'sex', 'civil_status',
            'citizenship', 'religion', 'occupation', 'mobile_no',
            'educational_attainment',
        ];
    }

    // ===== Import =====

    /**
     * Import households and their members.
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
            'household',
            "Imported {$result['imported']} household members from CSV"
            . ($result['skipped'] === [] ? '' : ' (' . count($result['skipped']) . ' skipped)')
        );

        return $result;
    }

    /**
     * @param  resource  $handle
     * @param  array<int, string>  $headerMap
     * @param  array<int, string>  $columns
     * @return array{imported: int, skipped: array<int, string>}
     */
    private function importRows($handle, array $headerMap, array $columns): array
    {
        $firstOrCreate = app(FirstOrCreateResident::class);

        $imported = 0;
        $skipped = [];
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

                $resident = $firstOrCreate->handle($values['resident']);

                $placement = $this->place($resident, $values, $rowNum);

                if ($placement !== null) {
                    $skipped[] = $placement;
                    continue;
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
                'The sheet has no household rows. Fill in the rows below the header and import it again.'
            );
        }

        return compact('imported', 'skipped');
    }

    /**
     * The household and resident values a sheet row writes, or the reason the
     * row cannot be read.
     *
     * @return array<string, mixed>|string
     */
    private function rowValues(array $data)
    {
        $resident = $this->residentValues($data);

        if (is_string($resident)) {
            return $resident;
        }

        if ($data['property'] === '') {
            return 'property is required.';
        }

        if (! ctype_digit($data['property']) || (int) $data['property'] < 1) {
            return 'property must be the whole number ID of a saved property.';
        }

        if (! Property::whereKey((int) $data['property'])->exists()) {
            return 'property ID ' . $data['property'] . ' does not match a saved property.';
        }

        $type = $this->typeValue($data['type']);

        if ($type === false) {
            return 'type must be one of ' . implode(', ', array_keys(HouseholdType::labels())) . ', or blank.';
        }

        $isHead = $this->headValue($data['is_head']);

        if ($isHead === null) {
            return 'is_head must be Yes or No.';
        }

        if (mb_strlen($data['relationship']) > 100) {
            return 'relationship is longer than 100 characters.';
        }

        return [
            'property'      => (int) $data['property'],
            'type'          => $type ?? HouseholdType::OWNER,
            'prev_address'  => $data['prev_address'] !== '' ? $data['prev_address'] : null,
            'is_head'       => $isHead,
            'relationship'  => $data['relationship'] !== '' ? $data['relationship'] : null,
            'resident'      => $resident,
        ];
    }

    /**
     * The resident columns a row writes, or the reason it cannot be read.
     *
     * @return array<string, mixed>|string
     */
    private function residentValues(array $data)
    {
        if ($data['surname'] === '' || $data['first_name'] === '') {
            return 'surname and first name are both required.';
        }

        $birthDate = $this->dateValue($data['birth_date']);

        if ($birthDate === null) {
            return 'birth_date is required and must be a real date written as YYYY-MM-DD or DD/MM/YYYY.';
        }

        $sex = $this->enumValue(Sex::labels(), $data['sex']);

        if ($sex === null) {
            return 'sex is required and must be one of ' . implode(', ', array_keys(Sex::labels())) . '.';
        }

        $civilStatus = $this->enumValue(CivilStatus::labels(), $data['civil_status']);

        if ($civilStatus === null) {
            return 'civil_status is required and must be one of '
                . implode(', ', array_keys(CivilStatus::labels())) . '.';
        }

        $education = $this->enumValue(EducationalAttainment::labels(), $data['educational_attainment']);

        if ($education === null && $data['educational_attainment'] !== '') {
            return 'educational_attainment must be one of '
                . implode(', ', array_keys(EducationalAttainment::labels())) . ', or blank.';
        }

        return [
            'surname'                => $data['surname'],
            'first_name'             => $data['first_name'],
            'middle_name'            => $data['middle_name'] ?: null,
            'suffix'                 => $data['suffix'] ?: null,
            'birth_date'             => $birthDate,
            'birth_place'            => $data['birth_place'] ?: null,
            'sex'                    => $sex,
            'civil_status'           => $civilStatus,
            'citizenship'            => $data['citizenship'] ?: null,
            'religion'               => $data['religion'] ?: null,
            'occupation'             => $data['occupation'] ?: null,
            'mobile_no'              => $data['mobile_no'] ?: null,
            'educational_attainment' => $education,
        ];
    }

    // ===== Export =====

    /**
     * Export every household and member as one row per person.
     */
    public function export(): string
    {
        $households = Household::with(['memberRecords.resident'])
            ->orderBy('created_at', 'desc')
            ->get();

        $columns = $this->getColumnMap();
        $rows = [];

        foreach ($households as $household) {
            foreach ($household->memberRecords as $member) {
                $resident = $member->resident;

                $rows[] = [
                    $household->property_id ?? '',
                    $household->type ?? '',
                    $household->prev_address ?? '',
                    $member->is_head ? 'Yes' : 'No',
                    $member->relationship ?? '',
                    $resident?->surname ?? '',
                    $resident?->first_name ?? '',
                    $resident?->middle_name ?? '',
                    $resident?->suffix ?? '',
                    $resident?->birth_date ? $resident->birth_date->format('Y-m-d') : '',
                    $resident?->birth_place ?? '',
                    $resident?->sex ?? '',
                    $resident?->civil_status ?? '',
                    $resident?->citizenship ?? '',
                    $resident?->religion ?? '',
                    $resident?->occupation ?? '',
                    $resident?->mobile_no ?? '',
                    $resident?->educational_attainment ?? '',
                ];
            }
        }

        return CsvHelper::build($columns, $rows);
    }

    // ===== Template =====

    public function getImportTemplate(): string
    {
        return CsvHelper::buildHeader($this->getColumnMap());
    }

    // ===== Helpers =====

    /**
     * Put the resident in the row's household, or say why it cannot be placed.
     *
     * @param  array<string, mixed>  $values
     */
    private function place(Resident $resident, array $values, int $rowNum): ?string
    {
        if (HouseholdMember::where('resident_id', $resident->id)->exists()) {
            return "Row {$rowNum}: {$resident->full_name} already belongs to a household.";
        }

        $household = Household::firstOrCreate(
            ['property_id' => $values['property']],
            [
                'type'         => $values['type'],
                'prev_address' => $values['prev_address'],
            ]
        );

        if ($values['is_head']) {
            $household->memberRecords()->update(['is_head' => false]);
        }

        HouseholdMember::create([
            'household_id' => $household->id,
            'resident_id'  => $resident->id,
            'relationship' => HouseholdMember::valueFrom($values['relationship']),
            'is_head'      => $values['is_head'],
        ]);

        return null;
    }

    /**
     * The stored household type for a cell, null for a blank cell, or false
     * when the cell names a type the enum does not have.
     *
     * @return string|null|false
     */
    private function typeValue(string $input)
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        foreach (HouseholdType::labels() as $value => $label) {
            if (strcasecmp($input, $value) === 0 || strcasecmp($input, $label) === 0) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Whether a cell marks the row as head, false when it clears it, or null
     * when the cell says something else.
     */
    private function headValue(string $input): ?bool
    {
        $value = strtolower(trim($input));

        if (in_array($value, ['yes', 'y', 'true', '1', 'head'], true)) {
            return true;
        }

        if (in_array($value, ['', 'no', 'n', 'false', '0', 'member'], true)) {
            return false;
        }

        return null;
    }

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
}
