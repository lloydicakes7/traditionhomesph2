<?php

namespace App\Services\Csv;

use App\Models\Address;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Utility\CsvHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CSV import/export operations for Properties.
 *
 * Import, export and template rows for the module, kept beside the
 * Actions and Queries that own the same records.
 */
class PropertyCsvService
{
    /** @var ActivityLogService */
    private $activityService;

    public function __construct(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    // =====================================================================
    // Column Definitions
    // =====================================================================

    public function getColumnMap(): array
    {
        return [
            'block', 'lot', 'address',
            'sqm', 'type', 'picture',
        ];
    }

    // =====================================================================
    // Import
    // =====================================================================

    /**
     * Import properties from a CSV file.
     *
     * @param  string  $filePath  Real path to the CSV file
     * @return array{imported: int, skipped: array}
     */
    public function import(string $filePath): array
    {
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

                if ($data['block'] === '' || $data['lot'] === '') {
                    $skipped[] = "Row {$rowNum}: block and lot are required.";
                    continue;
                }

                if (! is_numeric($data['block']) || ! is_numeric($data['lot'])) {
                    $skipped[] = "Row {$rowNum}: block and lot must be numbers.";
                    continue;
                }

                $addressId = $this->resolveAddressId($data['address']);

                if (! $addressId) {
                    $skipped[] = "Row {$rowNum}: could not match address \"{$data['address']}\"";
                    continue;
                }

                // Handle picture: if file doesn't exist in storage, set to null
                $picture = $data['picture'] ?? '';
                if ($picture !== '' && ! Storage::disk('public')->exists($picture)) {
                    $picture = null;
                }

                Property::create([
                    'block'      => (int) $data['block'],
                    'lot'        => (int) $data['lot'],
                    'address_id' => $addressId,
                    'sqm'        => $data['sqm'] !== '' ? floatval($data['sqm']) : null,
                    'type'       => $data['type'] ?: null,
                    'picture'    => $picture ?: null,
                ]);
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
            'property',
            "Imported {$imported} properties from CSV" . (! empty($skipped) ? " ({$skipped} skipped)" : '')
        );

        return compact('imported', 'skipped');
    }

    // =====================================================================
    // Export
    // =====================================================================

    /**
     * Export all properties to a CSV string.
     *
     * A download is not an activity the office records: the audit trail holds
     * what was changed, and a copy of a record changes nothing.
     *
     * The only record a row reads is the unit's address — a property holds no
     * `households` relation (a unit's household is `currentHousehold`), and
     * eager loading one that does not exist took the whole export down before it
     * wrote a row.
     *
     * @return string  CSV content
     */
    public function export(string $sortField = 'created_at', string $sortDirection = 'desc'): string
    {
        $properties = Property::with('address')
            ->orderBy($sortField, $sortDirection)
            ->get();

        $columns = $this->getColumnMap();

        $rows = [];
        foreach ($properties as $p) {
            $rows[] = [
                $p->block ?? '',
                $p->lot ?? '',
                $p->address?->label ?? '',
                $p->sqm ?? '',
                $p->type ?? '',
                $p->picture ?? '',
            ];
        }

        return CsvHelper::build($columns, $rows);
    }

    // =====================================================================
    // Template
    // =====================================================================

    /**
     * Generate the CSV import template header row.
     *
     * @return string  CSV header row
     */
    public function getImportTemplate(): string
    {
        return CsvHelper::buildHeader($this->getColumnMap());
    }

    // =====================================================================
    // Tools
    // =====================================================================

    /**
     * Resolve an address ID from a label string.
     *
     * @param  string  $addressStr  Address label to match
     * @return int|null
     */
    private function resolveAddressId(string $addressStr): ?int
    {
        $addressStr = trim($addressStr);
        if ($addressStr === '') return null;

        $address = Address::whereRaw('LOWER(label) = ?', [strtolower($addressStr)])->first();

        return $address->id ?? null;
    }
}
