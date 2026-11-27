<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function getAll(): Collection
    {
        return Location::orderBy('country')->orderBy('name')->get();
    }

    public function create(array $data): Location
    {
        $location = Location::create($data);

        $this->auditLogService->log('CREATED', 'Location', $location->id, [
            'name' => $location->name,
            'location_code' => $location->location_code,
        ]);

        return $location;
    }

    public function update(Location $location, array $data): Location
    {
        $location->update($data);

        $this->auditLogService->log('UPDATED', 'Location', $location->id, $data);

        return $location->fresh();
    }
}

