<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentExceptionRequest;
use App\Http\Requests\UpdateShipmentExceptionStatusRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ShipmentExceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected ShipmentExceptionService $exceptionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = ShipmentException::with(['shipment.carrier', 'shipment.customer'])
            ->latest('detected_at');

        if ($user->isCustomer()) {
            $query->whereHas('shipment', fn ($q) => $q->where('customer_id', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        $exceptions = $query->paginate(15)->withQueryString();

        return view('exceptions.index', compact('exceptions'));
    }

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

        return back()->with('success', 'Status exception berhasil diperbarui.');
    }
}
