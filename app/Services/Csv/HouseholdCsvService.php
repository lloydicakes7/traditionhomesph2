<?php

namespace App\Services\Csv;

use App\Actions\Homeowners\Residents\FirstOrCreateResident;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Utility\CsvHelper;
use Illuminate\Support\Facades\DB;

/**
 * CSV import/export operations for households and their members.
 *
 * A household is identified by its property, and every row is one member.
 * `is_head` marks the one row that becomes the family head, so a sheet can
 * describe a whole household without naming an account: the household exists
 * whether or not the head has registered a login.
 */
class HouseholdCsvService
{
    /** @var ActivityLogService */
    private $activityService;

    public function __construct(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    // ===== Columns =====

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
     * Import households and their members from a CSV file.
     *
     * @return array{imported: int, skipped: array}
     */
    public function import(string $filePath): array
    {
        $firstOrCreate = app(FirstOrCreateResident::class);

        $handle = CsvHelper::openRead($filePath);
        $columnMap = $this->getColumnMap();
        $headerMap = CsvHelper::parseHeader($handle, $columnMap);

        $imported = 0;
        $skipped = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $data = CsvHelper::mapRow($row, $headerMap, $columnMap);

                if (empty($data['surname']) && empty($data['first_name'])) {
                    continue;
                }

                if (empty($data['surname']) || empty($data['first_name'])) {
                    $skipped[] = "Row {$rowNum}: missing surname or first name.";
                    continue;
                }

                if (empty($data['property'])) {
                    $skipped[] = "Row {$rowNum}: missing property ID.";
                    continue;
                }

                $resident = $firstOrCreate->handle($this->residentData($data));

                $placement = $this->place($resident, $data, $rowNum);

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
        } finally {
            fclose($handle);
        }

        $this->activityService->log(
            auth()->user(),
            'imported',
            'household',
            "Imported {$imported} household members from CSV" . (! empty($skipped) ? ' (' . count($skipped) . ' skipped)' : '')
        );

        return compact('imported', 'skipped');
    }

    // ===== Export =====

    /**
     * Export every household and member as one row per person.
     *
     * @return string  CSV content
     */
    public function export(): string
    {
        $households = Household::with(['memberRecords.resident'])
            ->orderBy('created_at', 'desc')
            ->get();

        $columnMap = $this->getColumnMap();
        $rows = [];

        foreach ($households as $h) {
            foreach ($h->memberRecords as $mr) {
                $r = $mr->resident;

                $rows[] = [
                    $h->property_id ?? '',
                    $h->type ?? '',
                    $h->prev_address ?? '',
                    $mr->is_head ? 'Yes' : 'No',
                    $mr->relationship ?? '',
                    $r?->surname ?? '',
                    $r?->first_name ?? '',
                    $r?->middle_name ?? '',
                    $r?->suffix ?? '',
                    $r?->birth_date ? $r->birth_date->format('Y-m-d') : '',
                    $r?->birth_place ?? '',
                    $r?->sex ?? '',
                    $r?->civil_status ?? '',
                    $r?->citizenship ?? '',
                    $r?->religion ?? '',
                    $r?->occupation ?? '',
                    $r?->mobile_no ?? '',
                    $r?->educational_attainment ?? '',
                ];
            }
        }

        return CsvHelper::build($columnMap, $rows);
    }

    // ===== Template =====

    /**
     * Generate the CSV import template header row.
     *
     * @return string  CSV header row
     */
    public function getImportTemplate(): string
    {
        return CsvHelper::buildHeader($this->getColumnMap());
    }

    // ===== Helpers =====

    /**
     * Resolve the household for the row's property and attach the resident.
     *
     * @return string|null  Why the row was skipped, or null when it was placed
     */
    private function place(Resident $resident, array $data, int $rowNum): ?string
    {
        $existing = HouseholdMember::where('resident_id', $resident->id)->first();

        if ($existing) {
            return "Row {$rowNum}: {$resident->full_name} already belongs to a household.";
        }

        $household = Household::firstOrCreate(
            ['property_id' => (int) $data['property']],
            [
                'type'         => $data['type'] ?: 'owner',
                'prev_address' => $data['prev_address'] ?: null,
            ]
        );

        $isHead = $this->isHead($data['is_head'] ?? '');

        if ($isHead) {
            $household->memberRecords()->update(['is_head' => false]);
        }

        HouseholdMember::create([
            'household_id' => $household->id,
            'resident_id'  => $resident->id,
            'relationship' => HouseholdMember::valueFrom($data['relationship'] ?? null),
            'is_head'      => $isHead,
        ]);

        return null;
    }

    private function residentData(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'surname', 'first_name', 'middle_name', 'suffix', 'birth_date',
            'birth_place', 'sex', 'civil_status', 'citizenship', 'religion',
            'occupation', 'mobile_no', 'educational_attainment',
        ]));
    }

    private function isHead($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['yes', 'y', 'true', '1', 'head'], true);
    }
}
