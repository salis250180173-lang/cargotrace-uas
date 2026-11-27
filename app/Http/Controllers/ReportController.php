<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Shipment::query();
        if ($user->isCustomer()) {
            $baseQuery->where('customer_id', $user->id);
        }

        $totalShipments = (clone $baseQuery)->count();
        $deliveredCount = (clone $baseQuery)->where('current_status', 'DELIVERED')->count();
        $delayedCount = (clone $baseQuery)->where('is_delayed', true)->count();
        $onTimeRate = $totalShipments > 0 ? round((($totalShipments - $delayedCount) / $totalShipments) * 100, 1) : 100.0;

        $statusBreakdown = [
            'CREATED' => (clone $baseQuery)->where('current_status', 'CREATED')->count(),
            'BOOKED' => (clone $baseQuery)->where('current_status', 'BOOKED')->count(),
            'PICKED_UP' => (clone $baseQuery)->where('current_status', 'PICKED_UP')->count(),
            'IN_TRANSIT' => (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count(),
            'DELIVERED' => $deliveredCount,
        ];

        $carrierPerformance = Carrier::all()->map(function (Carrier $carrier) use ($user) {
            $q = Shipment::where('carrier_id', $carrier->id);
            if ($user->isCustomer()) {
                $q->where('customer_id', $user->id);
            }
            $total = (clone $q)->count();
            $delayed = (clone $q)->where('is_delayed', true)->count();
            $delivered = (clone $q)->where('current_status', 'DELIVERED')->count();

            return [
                'name' => $carrier->name,
                'code' => $carrier->code,
                'total' => $total,
                'delivered' => $delivered,
                'delayed' => $delayed,
                'delay_rate' => $total > 0 ? round(($delayed / $total) * 100, 1) : 0.0,
            ];
        });

        $exceptionSummary = ShipmentException::query()
            ->when($user->isCustomer(), fn ($q) => $q->whereHas('shipment', fn ($sq) => $sq->where('customer_id', $user->id)))
            ->selectRaw('exception_type, count(*) as total')
            ->groupBy('exception_type')
            ->pluck('total', 'exception_type');

        return view('reports.index', compact(
            'totalShipments',
            'deliveredCount',
            'delayedCount',
            'onTimeRate',
            'statusBreakdown',
            'carrierPerformance',
            'exceptionSummary'
        ));
    }
}

