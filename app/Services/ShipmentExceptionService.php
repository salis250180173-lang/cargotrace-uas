<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentExceptionService
{
    public const STATUS_OPEN = 'OPEN';
    public const STATUS_INVESTIGATING = 'INVESTIGATING';
    public const STATUS_RESOLVED = 'RESOLVED';

    public const STATUS_FLOW = [
        self::STATUS_OPEN => self::STATUS_INVESTIGATING,
        self::STATUS_INVESTIGATING => self::STATUS_RESOLVED,
        self::STATUS_RESOLVED => null,
    ];

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
                'status' => self::STATUS_OPEN,
                'notes' => $data['notes'] ?? null,
                'detected_at' => $data['detected_at'] ?? now(),
            ]);

            $this->auditLogService->log('EXCEPTION_REPORTED', 'ShipmentException', $exception->id, [
                'shipment_id' => $shipment->id,
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

    /**
     * Transition exception strictly: OPEN -> INVESTIGATING -> RESOLVED.
     *
     * @throws ValidationException
     */
    public function updateStatus(ShipmentException $exception, string $newStatus, ?string $notes = null): ShipmentException
    {
        return DB::transaction(function () use ($exception, $newStatus, $notes) {
            $currentStatus = $exception->status;

            if ($currentStatus === self::STATUS_RESOLVED) {
                throw ValidationException::withMessages([
                    'status' => 'Exception sudah RESOLVED dan tidak dapat diubah lagi.',
                ]);
            }

            $allowedNext = self::STATUS_FLOW[$currentStatus] ?? null;
            if ($newStatus !== $allowedNext) {
                throw ValidationException::withMessages([
                    'status' => "Transisi status exception tidak valid dari {$currentStatus} ke {$newStatus}. Alur wajib: OPEN -> INVESTIGATING -> RESOLVED.",
                ]);
            }

            $payload = [
                'status' => $newStatus,
                'notes' => $notes ?? $exception->notes,
            ];

            if ($newStatus === self::STATUS_RESOLVED) {
                $payload['resolved_at'] = now();
            }

            $exception->update($payload);

            $this->auditLogService->log('EXCEPTION_STATUS_UPDATED', 'ShipmentException', $exception->id, [
                'shipment_id' => $exception->shipment_id,
                'from' => $currentStatus,
                'to' => $newStatus,
            ]);

            $shipment = $exception->shipment;
            if ($shipment && $shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    "Exception Update: {$shipment->shipment_number}",
                    "Status kendala {$exception->exception_type} diperbarui menjadi {$newStatus}.",
                    $shipment->id
                );
            }

            return $exception->fresh();
        });
    }
}
