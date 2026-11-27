<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

    public function admin(): View
    {
        return view('admin.dashboard', [
            'totalShipments' => Shipment::count(),
            'delayedShipments' => Shipment::where('is_delayed', true)->count(),
            'openExceptions' => ShipmentException::where('status', '!=', 'RESOLVED')->count(),
            'totalCarriers' => Carrier::where('is_active', true)->count(),
            'totalLocations' => Location::count(),
            'totalUsers' => User::count(),
            'recentLogs' => AuditLog::with('user')->latest('created_at')->take(10)->get(),
        ]);
    }

    public function operator(): View
    {
        return view('operator.dashboard', [
            'shipments' => $this->shipmentService->queryForUser(auth()->user())->paginate(15),
            'delayedCount' => Shipment::where('is_delayed', true)->count(),
            'openExceptions' => ShipmentException::with('shipment')
                ->where('status', '!=', 'RESOLVED')
                ->latest('detected_at')
                ->take(10)
                ->get(),
        ]);
    }

    public function customer(Request $request): View
    {
        $user = $request->user();

        return view('customer.dashboard', [
            'shipments' => $this->shipmentService->queryForUser($user)->paginate(10),
            'unreadNotifications' => $user->notifications()->where('is_read', false)->count(),
        ]);
    }
}

