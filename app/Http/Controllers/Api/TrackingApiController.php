<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingEventService;
use Illuminate\Http\JsonResponse;

class TrackingApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected TrackingEventService $trackingEventService
    ) {}

    public function index(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $events = $shipment->trackingEvents()->with('creator')->get();

        return $this->success($events, 'Tracking events retrieved successfully.', 200);
    }

    public function store(StoreTrackingEventRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $event = $this->trackingEventService->addEvent($shipment, $request->validated());

        return $this->success($event, 'Tracking event recorded successfully.', 201);
    }
}

