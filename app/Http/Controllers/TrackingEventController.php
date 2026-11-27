<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingEventService;
use Illuminate\Http\RedirectResponse;

class TrackingEventController extends Controller
{
    public function __construct(
        protected TrackingEventService $trackingEventService
    ) {}

    public function store(StoreTrackingEventRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->trackingEventService->addEvent($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Tracking event baru berhasil ditambahkan.');
    }
}

