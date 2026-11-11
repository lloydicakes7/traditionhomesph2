<?php

namespace App\Actions\Homeowners\Residents;

use App\Models\Resident;
use Illuminate\Support\Facades\DB;

class FirstOrCreateResident
{
    // ===== Handle =====

    public function handle(array $data): Resident
    {
        $birthDate = $this->normalizeDate($data['birth_date']);

        $candidates = Resident::whereRaw('LOWER(TRIM(surname)) = ?', [strtolower(trim($data['surname']))])
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower(trim($data['first_name']))])
            ->get();

        foreach ($candidates as $resident) {
            if (
                $this->normalizeDate($resident->birth_date) === $birthDate &&
                strtolower(trim((string) $resident->sex)) === strtolower(trim($data['sex'])) &&
                strtolower(trim((string) $resident->middle_name)) === strtolower(trim($data['middle_name'] ?? '')) &&
                strtolower(trim((string) $resident->suffix)) === strtolower(trim($data['suffix'] ?? ''))
            ) {
                return $resident;
            }
        }

        return DB::transaction(function () use ($data) {
            return Resident::create(Resident::payloadFrom($data));
        });
    }

    // ===== Helpers =====

    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $date = trim($date);

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y', 'Y/m/d', 'd.m.Y'] as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            $errors = \DateTime::getLastErrors();
            $hasIssues = $errors && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);

            if ($parsed && ! $hasIssues) {
                return $parsed->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime($date))->format('Y-m-d');
        } catch (\Exception $e) {
            return $date;
        }
    }
}
