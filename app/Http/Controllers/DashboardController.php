<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    public function admin(Request $request): View
    {
        return $this->renderDashboard($request);
    }

    public function operator(Request $request): View
    {
        return $this->renderDashboard($request);
    }

    public function customer(Request $request): View
    {
        return $this->renderDashboard($request);
    }

    protected function renderDashboard(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Shipment::query();
        if ($user->isCustomer()) {
            $baseQuery->where('customer_id', $user->id);
        }

        // 5 Summary Cards (100% dynamic from MySQL)
        $totalShipments = (clone $baseQuery)->count();
        $inTransitCount = (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count();
        $deliveredCount = (clone $baseQuery)->where('current_status', 'DELIVERED')->count();
        $delayedCount = (clone $baseQuery)->where('is_delayed', true)->count();

        $exceptionsQuery = ShipmentException::where('status', '!=', 'RESOLVED');
        if ($user->isCustomer()) {
            $exceptionsQuery->whereHas('shipment', fn ($q) => $q->where('customer_id', $user->id));
        }
        $openExceptionsCount = $exceptionsQuery->count();

        // Bar Chart: Shipments by Status
        $statusChartData = [
            'labels' => ['In Transit', 'Delivered', 'Delayed', 'Open Exceptions'],
            'values' => [$inTransitCount, $deliveredCount, $delayedCount, $openExceptionsCount],
        ];

        // Doughnut Chart: Delay Rate by Carrier
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $carrierStats = [];
        $totalDelayedForRate = 0;
        $totalShipmentsForRate = 0;

        foreach ($carriers as $carrier) {
            $cQuery = (clone $baseQuery)->where('carrier_id', $carrier->id);
            $cTotal = (clone $cQuery)->count();
            $cDelayed = (clone $cQuery)->where('is_delayed', true)->count();
            $rate = $cTotal > 0 ? round(($cDelayed / $cTotal) * 100, 1) : 0.0;

            $carrierStats[] = [
                'name' => $carrier->name,
                'total' => $cTotal,
                'delayed' => $cDelayed,
                'rate' => $rate,
            ];

            $totalDelayedForRate += $cDelayed;
            $totalShipmentsForRate += $cTotal;
        }

        $averageDelayRate = $totalShipmentsForRate > 0
            ? round(($totalDelayedForRate / $totalShipmentsForRate) * 100, 1)
            : 0.0;

        // Mini Map: Active shipments with coordinates
        $mapShipments = (clone $baseQuery)
            ->with(['originLocation', 'destinationLocation', 'carrier', 'trackingEvents'])
            ->where('current_status', '!=', 'DELIVERED')
            ->latest()
            ->take(20)
            ->get()
            ->map(function (Shipment $s) {
                $latestEvent = $s->trackingEvents->last();

                return [
                    'id' => $s->id,
                    'shipment_number' => $s->shipment_number,
                    'status' => $s->current_status,
                    'is_delayed' => $s->is_delayed,
                    'carrier' => $s->carrier?->name,
                    'origin' => $s->originLocation ? [
                        'name' => $s->originLocation->name,
                        'lat' => (float) $s->originLocation->latitude,
                        'lng' => (float) $s->originLocation->longitude,
                    ] : null,
                    'destination' => $s->destinationLocation ? [
                        'name' => $s->destinationLocation->name,
                        'lat' => (float) $s->destinationLocation->latitude,
                        'lng' => (float) $s->destinationLocation->longitude,
                    ] : null,
                    'current' => ($latestEvent && $latestEvent->latitude !== null) ? [
                        'name' => $latestEvent->location_name,
                        'lat' => (float) $latestEvent->latitude,
                        'lng' => (float) $latestEvent->longitude,
                    ] : null,
                    'url' => route('shipments.show', $s),
                ];
            })->values();

        // Recent Shipments Table (Filter, Search, Sort, Server-side Pagination)
        $tableQuery = $this->shipmentService->queryForUser($user);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $tableQuery->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('carrier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('originLocation', fn ($oq) => $oq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('destinationLocation', fn ($dq) => $dq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'DELAYED') {
                $tableQuery->where('is_delayed', true);
            } else {
                $tableQuery->where('current_status', $request->input('status'));
            }
        }

        if ($request->filled('carrier_id')) {
            $tableQuery->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('origin_id')) {
            $tableQuery->where('origin_location_id', $request->input('origin_id'));
        }

        if ($request->filled('destination_id')) {
            $tableQuery->where('destination_location_id', $request->input('destination_id'));
        }

        $sortBy = in_array($request->input('sort_by'), ['shipment_number', 'expected_arrival', 'current_status', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $recentShipments = $tableQuery->reorder($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        $locations = Location::orderBy('country')->orderBy('name')->get();

        return view('dashboard', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'statusChartData',
            'carrierStats',
            'averageDelayRate',
            'mapShipments',
            'recentShipments',
            'carriers',
            'locations',
            'sortBy',
            'sortDir'
        ));
    }
}
