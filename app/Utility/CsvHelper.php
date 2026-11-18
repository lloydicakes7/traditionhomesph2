<?php

namespace App\Utility;

/**
 * Generic CSV reading and writing.
 *
 * It knows nothing about models or business rules: it opens a file, reads a
 * header, checks that header against the columns a sheet must have, maps a row
 * onto those columns, and writes a CSV string back.
 */
class CsvHelper
{
    /**
     * Open a CSV file for reading.
     *
     * @param  string  $filePath  Real path to the CSV file.
     * @return resource
     */
    public static function openRead(string $filePath)
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('The file could not be opened for reading.');
        }

        return $handle;
    }

    /**
     * Read the header row, skipping a leading Excel `sep=` line, a blank line
     * and the byte order mark Excel writes in front of the first cell.
     *
     * @return array<int, string>
     */
    public static function readHeader($handle): array
    {
        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null]) {
                continue;
            }

            if (count($row) === 1 && preg_match('/^sep=/i', trim((string) $row[0]))) {
                continue;
            }

            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);

            return $row;
        }

        return [];
    }

    /**
     * Read the header row and map it, refusing a file that is not the template.
     *
     * @param  resource  $handle
     * @param  array<int, string>  $columns
     * @return array<int, string>
     *
     * @throws \InvalidArgumentException
     */
    public static function headerMap($handle, array $columns): array
    {
        $header = self::readHeader($handle);
        $problem = self::headerProblem($header, $columns);

        if ($problem !== null) {
            throw new \InvalidArgumentException($problem);
        }

        return self::mapHeader($header, $columns);
    }

    /**
     * Say what is wrong with the header row, or null when it matches the
     * template: the same columns, once each, in any order.
     *
     * @param  array<int, string>  $header
     * @param  array<int, string>  $columns
     */
    public static function headerProblem(array $header, array $columns): ?string
    {
        if ($header === []) {
            return 'The file has no header row.';
        }

        $names = array_values(array_filter(array_map([self::class, 'normalizeCell'], $header)));

        $repeated = array_values(array_unique(array_diff_assoc($names, array_unique($names))));

        if ($repeated !== []) {
            return 'The header row repeats ' . self::listed($repeated, 'column') . '.';
        }

        $missing = array_values(array_diff($columns, $names));

        if ($missing !== []) {
            return 'The header row is missing ' . self::listed($missing, 'column') . '.';
        }

        $unexpected = array_values(array_diff($names, $columns));

        if ($unexpected !== []) {
            return 'The template does not have ' . self::listed($unexpected, 'column') . '.';
        }

        return null;
    }

    /**
     * Map each file column index to the template column it carries.
     *
     * @param  array<int, string>  $header
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    public static function mapHeader(array $header, array $columns): array
    {
        $map = [];

        foreach ($header as $index => $cell) {
            $name = self::normalizeCell($cell);

            if (in_array($name, $columns, true)) {
                $map[$index] = $name;
            }
        }

        return $map;
    }

    /**
     * Map one file row onto every template column, using an empty string for a
     * column the row does not reach.
     *
     * @param  array<int, string|null>  $row
     * @param  array<int, string>  $headerMap
     * @param  array<int, string>  $columns
     * @return array<string, string>
     */
    public static function mapRow(array $row, array $headerMap, array $columns): array
    {
        $data = array_fill_keys($columns, '');

        foreach ($headerMap as $index => $column) {
            $data[$column] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    /**
     * Build a single quoted CSV row.
     *
     * @param  array<int, mixed>  $fields
     */
    public static function buildRow(array $fields): string
    {
        $escaped = array_map(function ($value) {
            return '"' . str_replace('"', '""', (string) $value) . '"';
        }, $fields);

        return implode(',', $escaped) . "\n";
    }

    /**
     * Build a header row from column names.
     *
     * @param  array<int, string>  $columns
     */
    public static function buildHeader(array $columns): string
    {
        return implode(',', $columns) . "\n";
    }

    /**
     * Build a body from rows of raw field values.
     *
     * @param  array<int, array<int, mixed>>  $rows
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
     * Build a complete CSV string with its header.
     *
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function build(array $columns, array $rows): string
    {
        return static::buildHeader($columns) . static::buildBody($rows);
    }

    /**
     * Read a heading the way the template spells it: "First Name",
     * "first_name" and "FIRST-NAME" all become `first_name`.
     */
    private static function normalizeCell($cell): string
    {
        $cell = strtolower(trim((string) $cell));

        return trim(preg_replace('/[^a-z0-9]+/', '_', $cell), '_');
    }

    /**
     * Name the given columns in a sentence: `the column "a"`, or
     * `the columns "a" and "b"`.
     *
     * @param  array<int, string>  $names
     */
    private static function listed(array $names, string $noun): string
    {
        $quoted = array_map(function (string $name): string {
            return '"' . $name . '"';
        }, $names);

        $last = array_pop($quoted);

        $text = $quoted === [] ? $last : implode(', ', $quoted) . ' and ' . $last;

        return (count($names) === 1 ? $noun : $noun . 's') . ' ' . $text;
    }
}
