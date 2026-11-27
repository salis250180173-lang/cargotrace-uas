<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_PICKED_UP = 'PICKED_UP';
    public const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    public const STATUS_DELIVERED = 'DELIVERED';

    /**
     * Strict sequential status transition map.
     */
    public const STATUS_FLOW = [
        self::STATUS_CREATED => self::STATUS_BOOKED,
        self::STATUS_BOOKED => self::STATUS_PICKED_UP,
        self::STATUS_PICKED_UP => self::STATUS_IN_TRANSIT,
        self::STATUS_IN_TRANSIT => self::STATUS_DELIVERED,
        self::STATUS_DELIVERED => null,
    ];

    public function __construct(
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService,
        protected EtaService $etaService
    ) {}

    public function queryForUser(User $user): Builder
    {
        $query = Shipment::query()->with([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
        ]);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        return $query->latest();
    }

    /**
     * Validate strict sequential status transition.
     *
     * @throws ValidationException
     */
    public function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        if ($currentStatus === self::STATUS_DELIVERED) {
            throw ValidationException::withMessages([
                'current_status' => 'Status shipment sudah DELIVERED (terminal) dan tidak dapat diubah lagi.',
            ]);
        }

        $allowedNext = self::STATUS_FLOW[$currentStatus] ?? null;

        if ($newStatus !== $allowedNext) {
            throw ValidationException::withMessages([
                'current_status' => "Transisi status tidak valid dari {$currentStatus} ke {$newStatus}. Urutan wajib: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED.",
            ]);
        }
    }

    public function create(array $data, array $items = []): Shipment
    {
        return DB::transaction(function () use ($data, $items) {
            $data['shipment_number'] = $data['shipment_number'] ?? $this->generateShipmentNumber();
            $data['current_status'] = self::STATUS_CREATED;
            $data = $this->etaService->applyToShipmentData($data);

            $shipment = Shipment::create($data);

            foreach ($items as $item) {
                $shipment->items()->create($item);
            }

            $this->auditLogService->log('CREATED', 'Shipment', $shipment->id, [
                'shipment_number' => $shipment->shipment_number,
                'current_status' => $shipment->current_status,
                'is_delayed' => $shipment->is_delayed,
            ]);

            if ($shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    "Shipment Created: {$shipment->shipment_number}",
                    "Shipment {$shipment->shipment_number} telah dibuat dengan status CREATED.",
                    $shipment->id
                );
            }

            return $shipment->load(['items', 'carrier', 'originLocation', 'destinationLocation']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function update(Shipment $shipment, array $data): Shipment
    {
        return DB::transaction(function () use ($shipment, $data) {
            if (isset($data['current_status']) && $data['current_status'] !== $shipment->current_status) {
                $this->validateStatusTransition($shipment->current_status, $data['current_status']);
            }

            $data = $this->etaService->applyToShipmentData($data, $shipment);

            $oldValues = $shipment->only(array_keys($data));
            $shipment->update($data);

            $this->auditLogService->log('UPDATED', 'Shipment', $shipment->id, [
                'old' => $oldValues,
                'new' => $data,
            ]);

            return $shipment->fresh(['customer', 'carrier', 'originLocation', 'destinationLocation', 'items']);
        });
    }

    /**
     * @throws ValidationException
     */
    public function updateStatus(Shipment $shipment, string $newStatus): Shipment
    {
        return DB::transaction(function () use ($shipment, $newStatus) {
            $oldStatus = $shipment->current_status;
            $this->validateStatusTransition($oldStatus, $newStatus);

            $shipment->update(['current_status' => $newStatus]);

            $this->auditLogService->log('STATUS_UPDATED', 'Shipment', $shipment->id, [
                'from' => $oldStatus,
                'to' => $newStatus,
            ]);

            if ($shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    "Status Update: {$shipment->shipment_number}",
                    "Status pengiriman berubah dari {$oldStatus} menjadi {$newStatus}.",
                    $shipment->id
                );
            }

            return $shipment->fresh();
        });
    }

    public function delete(Shipment $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            $this->auditLogService->log('DELETED', 'Shipment', $shipment->id, [
                'shipment_number' => $shipment->shipment_number,
            ]);

            $shipment->delete();
        });
    }

    protected function generateShipmentNumber(): string
    {
        do {
            $number = 'CT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }
}
