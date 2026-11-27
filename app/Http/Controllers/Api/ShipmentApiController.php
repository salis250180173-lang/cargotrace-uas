<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->shipmentService->queryForUser($request->user());

        if ($request->filled('status')) {
            if ($request->input('status') === 'DELAYED') {
                $query->where('is_delayed', true);
            } else {
                $query->where('current_status', $request->input('status'));
            }
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $shipments = $query->paginate($perPage);

        return $this->success($shipments, 'Shipments retrieved successfully.', 200);
    }

    public function store(StoreShipmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $shipment = $this->shipmentService->create($validated, $items);

        return $this->success($shipment, 'Shipment created successfully.', 201);
    }

    public function show(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents',
            'exceptions',
        ]);

        return $this->success($shipment, 'Shipment retrieved successfully.', 200);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $updated = $this->shipmentService->update($shipment, $request->validated());

        return $this->success($updated, 'Shipment updated successfully.', 200);
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->delete($shipment);

        return $this->success(null, 'Shipment deleted successfully.', 200);
    }
}

