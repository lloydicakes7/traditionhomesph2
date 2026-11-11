<?php

namespace App\Utility;

/**
 * Generic, domain-agnostic helper for reading from and exporting to CSV files.
 *
 * Wraps low-level CSV parsing/writing operations (fgetcsv, fputcsv, header
 * mapping, field escaping) so that Services never repeat the same fopen/fgetcsv/
 * fclose pattern or manual CSV-escaping logic.
 *
 * Has no knowledge of Eloquent models, business rules, or column semantics —
 * it works entirely with arrays of strings.
 */
class CsvHelper
{
    /**
     * Open a CSV file and read the header row, returning a map of column
     * index → normalized column name for the given column map.
     *
     * @param  resource  $handle     An already-opened file handle.
     * @param  string[]  $columnMap  Allowed column names.
     * @return array<int, string>    Index → column name.
     */
    public static function parseHeader($handle, array $columnMap): array
    {
        $header = fgetcsv($handle);
        $headerMap = [];

        if ($header === false) {
            return [];
        }

        foreach ($header as $i => $col) {
            $normalized = strtolower(str_replace([' ', '/', '.'], '_', trim($col)));
            if (in_array($normalized, $columnMap, true)) {
                $headerMap[$i] = $normalized;
            }
        }

        return $headerMap;
    }

    /**
     * Read a single CSV data row and map it to the column map using the
     * header map. Returns an associative array keyed by column name.
     *
     * @param  array          $row        Raw CSV row from fgetcsv.
     * @param  array<int, string> $headerMap  Index → column name map.
     * @param  string[]       $columnMap  All column names to populate.
     * @return array<string, string>      Column name → trimmed value.
     */
    public static function mapRow(array $row, array $headerMap, array $columnMap): array
    {
        $data = array_fill_keys($columnMap, '');
        foreach ($headerMap as $i => $field) {
            $data[$field] = trim($row[$i] ?? '');
        }

        return $data;
    }

    /**
     * Match a file's header row against the names a column may be called.
     *
     * A statement's own headings are the bank's, not ours — one bank writes
     * "Transaction Date" where another writes "Date", one writes "Withdrawal"
     * where another writes "Debit" — so the caller says which names each
     * column may go by and this returns which file column is which, without
     * any knowledge of what the columns mean.
     *
     * @param  string[]                        $header    The file's header cells
     * @param  array<string, array<int,string>> $synonyms  Canonical name → the names it may go by
     * @return array<string, int>              Canonical name → file column index (only the ones found)
     */
    public static function detectColumns(array $header, array $synonyms): array
    {
        $normalized = [];

        foreach ($header as $index => $cell) {
            $normalized[self::normalize($cell)] = $index;
        }

        $detected = [];

        foreach ($synonyms as $name => $candidates) {
            foreach ($candidates as $candidate) {
                $key = self::normalize($candidate);

                if (array_key_exists($key, $normalized)) {
                    $detected[$name] = $normalized[$key];
                    break;
                }
            }
        }

        return $detected;
    }

    /**
     * Normalize a heading so that "Transaction Date", "transaction_date" and
     * "TRANSACTION-DATE" all read the same.
     */
    public static function normalize(?string $heading): string
    {
        $heading = strtolower(trim((string) $heading));

        return preg_replace('/[^a-z0-9]+/', '_', $heading);
    }

    /**
     * Open a CSV file for reading and return the handle.
     *
     * @param  string  $filePath  Real path to the CSV file.
     * @return resource
     */
    public static function openRead(string $filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file for reading: {$filePath}");
        }

        return $handle;
    }

    /**
     * Build a single CSV row with proper quoting and escaping.
     *
     * Each field is wrapped in double-quotes and any embedded double-quotes
     * are escaped (""). Fields are separated by commas.
     *
     * @param  array  $fields  Raw field values.
     * @return string          Escaped CSV row ending with newline.
     */
    public static function buildRow(array $fields): string
    {
        $escaped = array_map(function ($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $fields);

        return implode(',', $escaped) . "\n";
    }

    /**
     * Build a CSV header row from column names.
     *
     * @param  string[]  $columns  Column names.
     * @return string              CSV header row ending with newline.
     */
    public static function buildHeader(array $columns): string
    {
        return implode(',', $columns) . "\n";
    }

    /**
     * Build a complete CSV body from an array of row data arrays.
     *
     * Each row is an array of raw (unescaped) field values.
     *
     * @param  array[]  $rows  Array of field-value arrays.
     * @return string          Complete CSV body (no header).
     */
    public static function buildBody(array $rows): string
    {
        $csv = '';
        foreach ($rows as $row) {
            $csv .= static::buildRow($row);
        }

        return $csv;
    }

    /**
     * Build a complete CSV string with header and body.
     *
     * @param  string[]  $columns  Column names for the header row.
     * @param  array[]   $rows     Array of field-value arrays for the body.
     * @return string              Complete CSV string.
     */
    public static function build(array $columns, array $rows): string
    {
        return static::buildHeader($columns) . static::buildBody($rows);
    }
}
