<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    public function index(): View
    {
        $locations = $this->locationService->getAll();

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('admin.locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $this->locationService->create($request->validated());

        return redirect()->route('admin.locations.index')->with('success', 'Location berhasil ditambahkan.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $this->locationService->update($location, $request->validated());

        return redirect()->route('admin.locations.index')->with('success', 'Location berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->originShipments()->exists() || $location->destinationShipments()->exists()) {
            return back()->withErrors(['location' => 'Location tidak dapat dihapus karena masih digunakan oleh shipment.']);
        }

        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Location berhasil dihapus.');
    }
}

