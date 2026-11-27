<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackingEventService
{
    public function __construct(
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService
    ) {}

    public function addEvent(Shipment $shipment, array $data): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data) {
            $event = $shipment->trackingEvents()->create([
                'status' => $data['status'],
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_at' => $data['recorded_at'] ?? now(),
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            $shipment->update(['current_status' => $event->status]);

            $this->auditLogService->log('TRACKING_EVENT_ADDED', 'Shipment', $shipment->id, [
                'event_id' => $event->id,
                'status' => $event->status,
            ]);

            if ($shipment->customer) {
                $this->notificationService->notify(
                    $shipment->customer,
                    "Status Update: {$shipment->shipment_number}",
                    "Status pengiriman berubah menjadi {$event->status}.",
                    $shipment->id
                );
            }

            return $event;
        });
    }
}

