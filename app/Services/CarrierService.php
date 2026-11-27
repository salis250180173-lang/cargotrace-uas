<?php

namespace App\Services;

use App\Models\Carrier;
use Illuminate\Database\Eloquent\Collection;

class CarrierService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function getAll(): Collection
    {
        return Carrier::orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return Carrier::where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Carrier
    {
        $carrier = Carrier::create($data);

        $this->auditLogService->log('CREATED', 'Carrier', $carrier->id, [
            'name' => $carrier->name,
            'code' => $carrier->code,
        ]);

        return $carrier;
    }

    public function update(Carrier $carrier, array $data): Carrier
    {
        $carrier->update($data);

        $this->auditLogService->log('UPDATED', 'Carrier', $carrier->id, $data);

        return $carrier->fresh();
    }
}

