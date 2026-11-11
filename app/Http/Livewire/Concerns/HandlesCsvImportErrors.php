<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared trait for translating raw CSV import exceptions into
 * user-friendly error messages. Used by both HomeownerList
 * and PropertyList Livewire components.
 */
trait HandlesCsvImportErrors
{
    /**
     * Translate a raw exception into a user-friendly error message.
     *
     * @param  \Exception  $e       The caught exception
     * @param  int|null    $rowNum  CSV row number where the error occurred
     * @return string               Readable error message
     */
    protected function formatImportError(\Exception $e, ?int $rowNum = null): string
    {
        $prefix = $rowNum ? "Row {$rowNum}: " : '';

        // Only QueryException carries structured SQL error info
        if ($e instanceof \Illuminate\Database\QueryException) {
            $msg = $e->getMessage();

            // 1) Unique constraint violation (e.g. duplicate email)
            if (str_contains($msg, 'Duplicate entry')) {
                preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $msg, $m);
                $value = $m[1] ?? '?';
                $key   = $m[2] ?? '';
                $field = ucwords(str_replace(['_unique', '_'], ['', ' '], $key));
                return "{$prefix}A {$field} with \"{$value}\" already exists. Please use a different value.";
            }

            // 2) NOT NULL — missing required data
            if (str_contains($msg, 'cannot be null')) {
                preg_match("/Column '([^']+)' cannot be null/", $msg, $m);
                $field = ! empty($m[1]) ? ucwords(str_replace('_', ' ', $m[1])) : 'a field';
                return "{$prefix}Missing required data: \"{$field}\" is required but was empty.";
            }

            // 3) Column not found — CSV column name doesn't match the database
            if (str_contains($msg, 'Unknown column') || str_contains($msg, 'not found')) {
                preg_match("/Unknown column '([^']+)'/", $msg, $m);
                $column = $m[1] ?? '?';
                return "{$prefix}Column \"{$column}\" was not found in the database. Check that your CSV column names match the template.";
            }

            // 4) Foreign key constraint
            if (str_contains($msg, 'foreign key constraint')) {
                return "{$prefix}Invalid reference — the linked record does not exist. Check the imported data.";
            }

            // 5) Data too long
            if (str_contains($msg, 'Data too long')) {
                return "{$prefix}A value is too long for its column. Please shorten the data.";
            }

            // 6) Generic Integrity constraint violation — catches any 23000
            //    errors that didn't match the specific patterns above.
            if (str_contains($msg, 'Integrity constraint violation')) {
                return "{$prefix}A database constraint was violated. Check that all required fields are filled in, values are unique, and references are valid.";
            }

            // 7) Fallback — truncated SQL error
            return "{$prefix}A database error occurred: " . substr($msg, 0, 200);
        }

        // Non-query exception (PHP errors, validation failures, etc.)
        return $prefix . $e->getMessage();
    }
}
