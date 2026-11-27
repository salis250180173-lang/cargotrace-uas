<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Support\Facades\DB;

class ShipmentExceptionService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService
    ) {}

    public function report(Shipment $shipment, array $data): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data) {
            $exception = $shipment->exceptions()->create([
                'exception_type' => $data['exception_type'],
                'severity' => $data['severity'],
                'status' => $data['status'] ?? 'OPEN',
                'notes' => $data['notes'] ?? null,
                'detected_at' => $data['detected_at'] ?? now(),
            ]);

            $this->auditLogService->log('EXCEPTION_REPORTED', 'Shipment', $shipment->id, [
                'exception_id' => $exception->id,
                'exception_type' => $exception->exception_type,
                'severity' => $exception->severity,
            ]);

            if ($shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    "Shipment Exception: {$shipment->shipment_number}",
                    "Terdeteksi kendala {$exception->exception_type} (Severity: {$exception->severity}).",
                    $shipment->id
                );
            }

            return $exception;
        });
    }

    public function resolve(ShipmentException $exception, ?string $notes = null): ShipmentException
    {
        $exception->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
            'notes' => $notes ?? $exception->notes,
        ]);

        $this->auditLogService->log('EXCEPTION_RESOLVED', 'ShipmentException', $exception->id, [
            'shipment_id' => $exception->shipment_id,
        ]);

        return $exception->fresh();
    }
}

