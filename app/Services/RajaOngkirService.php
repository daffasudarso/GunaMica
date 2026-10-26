<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    public function searchDestinations(string $query, int $limit = 10): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['ok' => false, 'message' => 'Kata kunci tujuan wajib diisi.', 'data' => []];
        }

        $apiKey = (string) env('RAJAONGKIR_API_KEY', '');
        $baseUrl = rtrim((string) env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'), '/');

        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'RAJAONGKIR_API_KEY belum diset.', 'data' => []];
        }

        $response = Http::withHeaders([
            'key' => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->get($baseUrl . '/destination/domestic-destination', [
            'search' => $query,
            'limit' => $limit,
        ]);

        if (!$response->successful()) {
            return ['ok' => false, 'message' => 'Gagal mengambil data destinasi.', 'data' => []];
        }

        $json = $response->json();
        if (!is_array($json) || (int) data_get($json, 'meta.code', 0) !== 200) {
            return [
                'ok' => false,
                'message' => (string) data_get($json, 'meta.message', 'Response destinasi tidak valid.'),
                'data' => [],
            ];
        }

        return ['ok' => true, 'message' => 'ok', 'data' => (array) ($json['data'] ?? [])];
    }

    public function estimateCost(string $destinationId, int $weightGram, string $courier = 'jne'): array
    {
        $apiKey = (string) env('RAJAONGKIR_API_KEY', '');
        $baseUrl = rtrim((string) env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $originCity = (string) env('RAJAONGKIR_ORIGIN_CITY', '');

        if ($apiKey === '' || $originCity === '') {
            return ['ok' => false, 'message' => 'Konfigurasi RajaOngkir belum lengkap.', 'data' => []];
        }

        $response = Http::withHeaders([
            'key' => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($baseUrl . '/calculate/domestic-cost', [
            'origin' => $originCity,
            'destination' => $destinationId,
            'weight' => max(1, $weightGram),
            'courier' => strtolower($courier),
        ]);

        if (!$response->successful()) {
            return ['ok' => false, 'message' => 'Gagal hitung ongkir.', 'data' => []];
        }

        $json = $response->json();
        if (!is_array($json) || (int) data_get($json, 'meta.code', 0) !== 200) {
            return [
                'ok' => false,
                'message' => (string) data_get($json, 'meta.message', 'Response ongkir tidak valid.'),
                'data' => [],
            ];
        }

        $rows = (array) ($json['data'] ?? []);
        $services = [];

        foreach ($rows as $row) {
            $cost = (int) ($row['cost'] ?? 0);
            $service = strtoupper((string) ($row['service'] ?? ''));
            if ($cost <= 0 || $service === '') {
                continue;
            }

            $services[] = [
                'courier_code' => strtolower((string) ($row['code'] ?? $courier)),
                'courier_name' => (string) ($row['name'] ?? strtoupper($courier)),
                'service_code' => $service,
                'service_name' => (string) ($row['description'] ?? $service),
                'cost' => $cost,
                'etd' => trim((string) ($row['etd'] ?? '-')),
            ];
        }

        return ['ok' => true, 'message' => 'ok', 'data' => $services];
    }
}
