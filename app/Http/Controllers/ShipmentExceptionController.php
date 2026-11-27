<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentExceptionRequest;
use App\Http\Requests\UpdateShipmentExceptionStatusRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ShipmentExceptionService;
use Illuminate\Http\RedirectResponse;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected ShipmentExceptionService $exceptionService
    ) {}

    public function store(StoreShipmentExceptionRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->exceptionService->report($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Exception berhasil dilaporkan.');
    }

    public function updateStatus(UpdateShipmentExceptionStatusRequest $request, ShipmentException $exception): RedirectResponse
    {
        $this->authorize('update', $exception->shipment);

        $validated = $request->validated();
        $this->exceptionService->updateStatus(
            $exception,
            $validated['status'],
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('shipments.show', $exception->shipment)
            ->with('success', 'Status exception berhasil diperbarui.');
    }
}

