<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentExceptionRequest;
use App\Http\Requests\UpdateShipmentExceptionStatusRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ShipmentExceptionService;
use Illuminate\Http\JsonResponse;

class ExceptionApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ShipmentExceptionService $exceptionService
    ) {}

    public function index(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $exceptions = $shipment->exceptions()->latest('detected_at')->get();

        return $this->success($exceptions, 'Shipment exceptions retrieved successfully.', 200);
    }

    public function store(StoreShipmentExceptionRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $exception = $this->exceptionService->report($shipment, $request->validated());

        return $this->success($exception, 'Shipment exception reported successfully.', 201);
    }

    public function update(UpdateShipmentExceptionStatusRequest $request, ShipmentException $exception): JsonResponse
    {
        $this->authorize('update', $exception->shipment);

        $validated = $request->validated();
        $updated = $this->exceptionService->updateStatus(
            $exception,
            $validated['status'],
            $validated['notes'] ?? null
        );

        return $this->success($updated, 'Shipment exception updated successfully.', 200);
    }
}

