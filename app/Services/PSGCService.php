<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Log;

class PSGCService
{
    const API_BASE_URL = 'https://psgc.cloud/api/';

    private function cached(string $key, string $url): array
    {
        return Cache::remember("psgc:{$key}", now()->addDays(30), function () use ($url, $key) {
            try {
                $response = Http::timeout(10)->get($url);

                if (! $response->successful()) {
                    return null;
                }

                return array_map(function ($row) {
                    $row['name'] = trim($row['name']);
                    return $row;
                }, $response->json());
            } catch (Exception $e) {
                Log::warning("PSGC fetch failed for {$key}: " . $e->getMessage());
                return null;
            }
        }) ?? [];
    }

    public function fetchRegions(): array
    {
        return $this->cached('regions', self::API_BASE_URL . 'regions');
    }

    public function fetchProvinces(string $regionCode): array
    {
        return $this->cached("provinces:{$regionCode}", self::API_BASE_URL . "regions/{$regionCode}/provinces");
    }

    public function fetchCitiesMunicipalities(string $provinceCode): array
    {
        $cities = $this->cached("cities:{$provinceCode}", self::API_BASE_URL . "provinces/{$provinceCode}/cities-municipalities");

        return array_map(function ($c) {
            $c['name'] = self::formatCityName($c['name']);
            return $c;
        }, $cities);
    }

    public function fetchBarangays(string $cityCode): array
    {
        return $this->cached("barangays:{$cityCode}", self::API_BASE_URL . "cities-municipalities/{$cityCode}/barangays");
    }

    public static function formatCityName(?string $name): string
    {
        $name = trim((string) $name);

        if (preg_match('/^City of\s+(.+)$/i', $name, $matches)) {
            return trim($matches[1]) . ' City';
        }

        return $name;
    }
}
