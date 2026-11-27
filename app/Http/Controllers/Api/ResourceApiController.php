<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrierRequest;
use App\Http\Requests\StoreLocationRequest;
use App\Services\CarrierService;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;

class ResourceApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CarrierService $carrierService,
        protected LocationService $locationService
    ) {}

    public function carriers(): JsonResponse
    {
        return $this->success($this->carrierService->getAll(), 'Carriers retrieved successfully.', 200);
    }

    public function storeCarrier(StoreCarrierRequest $request): JsonResponse
    {
        $carrier = $this->carrierService->create($request->validated());

        return $this->success($carrier, 'Carrier created successfully.', 201);
    }

    public function locations(): JsonResponse
    {
        return $this->success($this->locationService->getAll(), 'Locations retrieved successfully.', 200);
    }

    public function storeLocation(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->locationService->create($request->validated());

        return $this->success($location, 'Location created successfully.', 201);
    }
}

