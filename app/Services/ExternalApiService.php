<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalApiService
{
    /**
     * Fetch current weather for given coordinates via OpenWeatherMap API.
     * Uses 5-second timeout and graceful fallback on failure or missing API key.
     */
    public function getWeather(?float $latitude, ?float $longitude, ?string $locationName = null): array
    {
        $apiKey = config('cargotrace.openweathermap.api_key');
        $baseUrl = rtrim((string) config('cargotrace.openweathermap.base_url'), '/');
        $timeout = (int) config('cargotrace.openweathermap.timeout', 5);

        if (empty($apiKey) || $latitude === null || $longitude === null) {
            return [
                'available' => false,
                'location' => $locationName,
                'message' => empty($apiKey)
                    ? 'OpenWeatherMap API key belum dikonfigurasi.'
                    : 'Koordinat lokasi tidak tersedia.',
            ];
        }

        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}/weather", [
                'lat' => $latitude,
                'lon' => $longitude,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if (! $response->successful()) {
                return [
                    'available' => false,
                    'location' => $locationName,
                    'message' => "OpenWeatherMap API merespons dengan status {$response->status()}.",
                ];
            }

            $json = $response->json();

            return [
                'available' => true,
                'location' => $json['name'] ?? $locationName,
                'temperature' => $json['main']['temp'] ?? null,
                'humidity' => $json['main']['humidity'] ?? null,
                'condition' => $json['weather'][0]['main'] ?? null,
                'description' => $json['weather'][0]['description'] ?? null,
                'wind_speed' => $json['wind']['speed'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::warning('OpenWeatherMap API error: ' . $e->getMessage());

            return [
                'available' => false,
                'location' => $locationName,
                'message' => 'Layanan cuaca sedang tidak tersedia (fallback aktif).',
            ];
        }
    }

    /**
     * Convert shipping_cost between global currencies via ExchangeRate API.
     * Uses 5-second timeout and graceful fallback on failure or missing API key.
     */
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): array
    {
        $from = strtoupper(trim($fromCurrency));
        $to = strtoupper(trim($toCurrency));

        if ($from === $to) {
            return [
                'available' => true,
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'rate' => 1.0,
                'converted_amount' => round($amount, 2),
            ];
        }

        $apiKey = config('cargotrace.exchangerate.api_key');
        $baseUrl = rtrim((string) config('cargotrace.exchangerate.base_url'), '/');
        $timeout = (int) config('cargotrace.exchangerate.timeout', 5);

        if (empty($apiKey)) {
            return [
                'available' => false,
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'rate' => null,
                'converted_amount' => null,
                'message' => 'ExchangeRate API key belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::timeout($timeout)->get("{$baseUrl}/{$apiKey}/pair/{$from}/{$to}/{$amount}");

            if (! $response->successful()) {
                return [
                    'available' => false,
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'rate' => null,
                    'converted_amount' => null,
                    'message' => "ExchangeRate API merespons dengan status {$response->status()}.",
                ];
            }

            $json = $response->json();

            if (($json['result'] ?? '') !== 'success') {
                return [
                    'available' => false,
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'rate' => null,
                    'converted_amount' => null,
                    'message' => $json['error-type'] ?? 'Konversi mata uang gagal.',
                ];
            }

            return [
                'available' => true,
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'rate' => (float) ($json['conversion_rate'] ?? 0),
                'converted_amount' => round((float) ($json['conversion_result'] ?? 0), 2),
            ];
        } catch (Throwable $e) {
            Log::warning('ExchangeRate API error: ' . $e->getMessage());

            return [
                'available' => false,
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'rate' => null,
                'converted_amount' => null,
                'message' => 'Layanan kurs mata uang sedang tidak tersedia (fallback aktif).',
            ];
        }
    }
}

