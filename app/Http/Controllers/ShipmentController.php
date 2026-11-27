<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Requests\UpdateShipmentStatusRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Services\CarrierService;
use App\Services\ExternalApiService;
use App\Services\LocationService;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected CarrierService $carrierService,
        protected LocationService $locationService,
        protected ExternalApiService $externalApiService
    ) {}

    public function index(Request $request): View
    {
        $shipments = $this->shipmentService
            ->queryForUser($request->user())
            ->paginate(15);

        return view('shipments.index', compact('shipments'));
    }

    public function create(): View
    {
        $this->authorize('create', Shipment::class);

        return view('shipments.create', [
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(),
            'carriers' => $this->carrierService->getActive(),
            'locations' => $this->locationService->getAll(),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $shipment = $this->shipmentService->create($validated, $items);

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} berhasil dibuat.");
    }

    public function show(Request $request, Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents.creator',
            'exceptions',
        ]);

        $latestEvent = $shipment->trackingEvents->last();
        $weatherLat = $latestEvent?->latitude ?? $shipment->destinationLocation?->latitude;
        $weatherLon = $latestEvent?->longitude ?? $shipment->destinationLocation?->longitude;
        $weatherLocation = $latestEvent?->location_name ?? $shipment->destinationLocation?->name;

        $weather = $this->externalApiService->getWeather(
            $weatherLat !== null ? (float) $weatherLat : null,
            $weatherLon !== null ? (float) $weatherLon : null,
            $weatherLocation
        );

        $targetCurrency = $request->query('currency');
        $convertedCost = null;
        if ($targetCurrency && $shipment->shipping_cost !== null) {
            $convertedCost = $this->externalApiService->convertCurrency(
                (float) $shipment->shipping_cost,
                $shipment->currency_code,
                $targetCurrency
            );
        }

        return view('shipments.show', compact('shipment', 'weather', 'convertedCost', 'targetCurrency'));
    }

    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        return view('shipments.edit', [
            'shipment' => $shipment->load('items'),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(),
            'carriers' => $this->carrierService->getActive(),
            'locations' => $this->locationService->getAll(),
        ]);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->shipmentService->update($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} berhasil diperbarui.");
    }

    public function updateStatus(UpdateShipmentStatusRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->shipmentService->updateStatus($shipment, $request->validated('current_status'));

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Status shipment {$shipment->shipment_number} berhasil diperbarui.");
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->delete($shipment);

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Shipment berhasil dihapus.');
    }
}

