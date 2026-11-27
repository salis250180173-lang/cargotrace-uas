<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Admin & Operator can view all shipments; Customer can only view their own.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $user->isCustomer() && $shipment->customer_id === $user->id;
    }

    /**
     * Only Admin & Operator can create shipments.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Only Admin & Operator can update shipments, add tracking events, or manage exceptions.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Only Admin can delete shipments.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}

