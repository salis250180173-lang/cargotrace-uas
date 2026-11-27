<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService
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

    public function create(array $data, array $items = []): Shipment
    {
        return DB::transaction(function () use ($data, $items) {
            $data['shipment_number'] = $data['shipment_number'] ?? $this->generateShipmentNumber();

            $shipment = Shipment::create($data);

            foreach ($items as $item) {
                $shipment->items()->create($item);
            }

            $this->auditLogService->log('CREATED', 'Shipment', $shipment->id, [
                'shipment_number' => $shipment->shipment_number,
            ]);

            if ($shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    'Shipment Created',
                    "Shipment {$shipment->shipment_number} telah dibuat dan sedang diproses.",
                    $shipment->id
                );
            }

            return $shipment->load(['items', 'carrier', 'originLocation', 'destinationLocation']);
        });
    }

    public function update(Shipment $shipment, array $data): Shipment
    {
        $shipment->update($data);

        $this->auditLogService->log('UPDATED', 'Shipment', $shipment->id, $data);

        return $shipment->fresh();
    }

    protected function generateShipmentNumber(): string
    {
        do {
            $number = 'CT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }
}

