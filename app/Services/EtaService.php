<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class EtaService
{
    /**
     * Calculate delay metrics between expected_arrival and estimated_arrival.
     *
     * Severity rules:
     * - 0 hours or earlier: NONE (is_delayed = false)
     * - >0 to 6 hours: LOW
     * - >6 to 24 hours: MEDIUM
     * - >24 hours: HIGH
     */
    public function calculateDelay(?CarbonInterface $expectedArrival, ?CarbonInterface $estimatedArrival): array
    {
        if (! $expectedArrival || ! $estimatedArrival || $estimatedArrival->lte($expectedArrival)) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0.0,
                'delay_severity' => 'NONE',
                'exceeds_threshold' => false,
            ];
        }

        $delayHours = round($expectedArrival->floatDiffInHours($estimatedArrival), 2);
        $threshold = (float) config('cargotrace.delay_threshold_hours', 12);

        $severity = match (true) {
            $delayHours <= 6 => 'LOW',
            $delayHours <= 24 => 'MEDIUM',
            default => 'HIGH',
        };

        return [
            'is_delayed' => true,
            'delay_hours' => $delayHours,
            'delay_severity' => $severity,
            'exceeds_threshold' => $delayHours >= $threshold,
        ];
    }

    /**
     * Compute and merge delay attributes into shipment payload.
     */
    public function applyToShipmentData(array $data, ?Shipment $existing = null): array
    {
        $expectedRaw = $data['expected_arrival'] ?? $existing?->expected_arrival;
        $estimatedRaw = $data['estimated_arrival'] ?? $existing?->estimated_arrival;

        $expected = $expectedRaw ? Carbon::parse($expectedRaw) : null;
        $estimated = $estimatedRaw ? Carbon::parse($estimatedRaw) : null;

        $delay = $this->calculateDelay($expected, $estimated);

        $data['is_delayed'] = $delay['is_delayed'];
        $data['delay_hours'] = $delay['delay_hours'];
        $data['delay_severity'] = $delay['delay_severity'];

        return $data;
    }
}

