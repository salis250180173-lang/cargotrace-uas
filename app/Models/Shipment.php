<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shipment_number',
    'customer_id',
    'carrier_id',
    'origin_location_id',
    'destination_location_id',
    'cargo_type',
    'weight',
    'volume',
    'shipping_cost',
    'currency_code',
    'expected_departure',
    'expected_arrival',
    'estimated_arrival',
    'current_status',
    'is_delayed',
    'delay_hours',
    'delay_severity',
])]
class Shipment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'volume' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'is_delayed' => 'boolean',
            'delay_hours' => 'decimal:2',
        ];
    }

    // ── Relationships ────────────────────────────────────────
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_id')->orderBy('recorded_at', 'asc');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'shipment_id');
    }
}

