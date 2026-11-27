<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\TrackingEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingEventController extends Controller
{
    public function __construct(
        protected TrackingEventService $trackingEventService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $eventsQuery = TrackingEvent::with(['shipment.carrier', 'shipment.originLocation', 'shipment.destinationLocation', 'creator'])
            ->latest('recorded_at');

        if ($user->isCustomer()) {
            $eventsQuery->whereHas('shipment', fn ($q) => $q->where('customer_id', $user->id));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $eventsQuery->where(function ($q) use ($search) {
                $q->where('location_name', 'like', "%{$search}%")
                    ->orWhereHas('shipment', fn ($sq) => $sq->where('shipment_number', 'like', "%{$search}%"));
            });
        }

        $events = $eventsQuery->paginate(20)->withQueryString();

        return view('tracking.index', compact('events'));
    }

    public function store(StoreTrackingEventRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->trackingEventService->addEvent($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Tracking event baru berhasil ditambahkan.');
    }
}
