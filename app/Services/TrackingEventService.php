<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackingEventService
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected AuditLogService $auditLogService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Append-only: add a new historical tracking event to a shipment.
     *
     * @throws ValidationException
     */
    public function addEvent(Shipment $shipment, array $data): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data) {
            $recordedAt = isset($data['recorded_at'])
                ? Carbon::parse($data['recorded_at'])
                : now();

            $latestEvent = $shipment->trackingEvents()
                ->reorder('recorded_at', 'desc')
                ->orderByDesc('id')
                ->first();

            if ($latestEvent && $recordedAt->lt($latestEvent->recorded_at)) {
                throw ValidationException::withMessages([
                    'recorded_at' => "Waktu event ({$recordedAt->toDateTimeString()}) tidak boleh lebih lampau dari event terakhir ({$latestEvent->recorded_at->toDateTimeString()}).",
                ]);
            }

            $newStatus = $data['status'];
            if ($newStatus !== $shipment->current_status) {
                $this->shipmentService->validateStatusTransition($shipment->current_status, $newStatus);
            }

            $event = $shipment->trackingEvents()->create([
                'status' => $newStatus,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_at' => $recordedAt,
                'created_by' => $data['created_by'] ?? Auth::id(),
            ]);

            if ($newStatus !== $shipment->current_status) {
                $shipment->update(['current_status' => $newStatus]);
            }

            $this->auditLogService->log('TRACKING_EVENT_ADDED', 'Shipment', $shipment->id, [
                'event_id' => $event->id,
                'status' => $event->status,
                'location_name' => $event->location_name,
                'recorded_at' => $recordedAt->toIso8601String(),
            ]);

            if ($shipment->customer) {
                $locationInfo = $event->location_name ? " di {$event->location_name}" : '';
                $this->notificationService->notify(
                    $shipment->customer,
                    "Tracking Update: {$shipment->shipment_number}",
                    "Status pengiriman: {$event->status}{$locationInfo}.",
                    $shipment->id
                );
            }

            return $event->load('creator');
        });
    }
}
