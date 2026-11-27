<?php

namespace App\Services\Csv;

use App\Enums\PropertyType;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Utility\CsvHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Properties CSV import, export and template.
 *
 * A row is skipped, with the reason and its line number, when the block or lot
 * is not a valid unit code, the address does not match a saved address, the
 * type is not one the Properties page offers, or the unit is already saved.
 */
class PropertyCsvService
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
            'block', 'lot', 'address',
            'sqm', 'type', 'picture',
        ];
    }

    // ===== Import =====

    /**
     * Import a properties sheet.
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
            'property',
            "Imported {$result['imported']} properties from CSV"
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
        $imported = 0;
        $skipped = [];
        $seen = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $data = CsvHelper::mapRow($row, $headerMap, $columns);

                if ($data['block'] === '' && $data['lot'] === '') {
                    continue;
                }

                $values = $this->rowValues($data);

                if (is_string($values)) {
                    $skipped[] = "Row {$rowNum}: {$values}";
                    continue;
                }

                $key = $values['block'] . '|' . $values['lot'];

                if (isset($seen[$key])) {
                    $skipped[] = "Row {$rowNum}: block {$values['block']} lot {$values['lot']} is listed earlier in the same file.";
                    continue;
                }

                if (Property::where('block', $values['block'])->where('lot', $values['lot'])->exists()) {
                    $skipped[] = "Row {$rowNum}: block {$values['block']} lot {$values['lot']} is already saved.";
                    continue;
                }

                Property::create($values);

                $seen[$key] = true;
                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        if ($imported === 0 && $skipped === []) {
            throw new \InvalidArgumentException(
                'The sheet has no property rows. Fill in the rows below the header and import it again.'
            );
        }

        return compact('imported', 'skipped');
    }

    /**
     * The property columns a sheet row writes, or the reason it cannot be read.
     *
     * @return array<string, mixed>|string
     */
    private function rowValues(array $data)
    {
        if ($data['block'] === '' || $data['lot'] === '') {
            return 'block and lot are both required.';
        }

        $block = trim($data['block']);
        $lot = strtoupper(trim($data['lot']));

        if (! ctype_digit($block) || strlen($block) > 20) {
            return 'block must use numbers only, at most 20 characters.';
        }

        if (! preg_match('/^[A-Z0-9]+$/', $lot) || strlen($lot) > 20) {
            return 'lot must use capital letters and numbers only, at most 20 characters.';
        }

        if ($data['sqm'] !== '' && (! is_numeric($data['sqm']) || (float) $data['sqm'] < 0)) {
            return 'sqm must be a number and cannot be negative.';
        }

        $type = $this->typeValue($data['type']);

        if ($type === false) {
            return 'type must be one of ' . implode(', ', array_keys(PropertyType::labels())) . ', or blank.';
        }

        $addressId = $this->resolveAddressId($data['address']);

        if ($addressId === null) {
            return $data['address'] === ''
                ? 'address is required.'
                : 'address "' . $data['address'] . '" does not match a saved address.';
        }

        return [
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $addressId,
            'sqm'        => $data['sqm'] !== '' ? (float) $data['sqm'] : null,
            'type'       => $type,
            'picture'    => $this->picturePath($data['picture']),
        ];
    }

    // ===== Export =====

    /**
     * Export every property, one row per unit.
     */
    public function export(string $sortField = 'created_at', string $sortDirection = 'desc'): string
    {
        $properties = Property::with('address')
            ->orderBy($sortField, $sortDirection)
            ->get();

        $columns = $this->getColumnMap();

        $rows = [];

        foreach ($properties as $property) {
            $rows[] = [
                $property->block ?? '',
                $property->lot ?? '',
                $property->address?->label ?? '',
                $property->sqm ?? '',
                $property->type ?? '',
                $property->picture ?? '',
            ];
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
     * The stored type for a cell, null for a blank cell, or false when the cell
     * names a type the enum does not have.
     *
     * @return string|null|false
     */
    private function typeValue(string $input)
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        foreach (PropertyType::labels() as $value => $label) {
            if (strcasecmp($input, $value) === 0 || strcasecmp($input, $label) === 0) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Match an address by its label, ignoring case.
     */
    private function resolveAddressId(string $label): ?int
    {
        $label = trim($label);

        if ($label === '') {
            return null;
        }

        $address = Address::whereRaw('LOWER(label) = ?', [strtolower($label)])->first();

        return $address ? $address->id : null;
    }

    private function picturePath(string $input): ?string
    {
        $path = trim($input);

        return $path !== '' && Storage::disk('public')->exists($path) ? $path : null;
    }
}
