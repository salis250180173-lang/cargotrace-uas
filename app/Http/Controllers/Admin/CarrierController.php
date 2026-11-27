<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrierRequest;
use App\Http\Requests\UpdateCarrierRequest;
use App\Models\Carrier;
use App\Services\CarrierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CarrierController extends Controller
{
    public function __construct(
        protected CarrierService $carrierService
    ) {}

    public function index(): View
    {
        $carriers = $this->carrierService->getAll();

        return view('admin.carriers.index', compact('carriers'));
    }

    public function create(): View
    {
        return view('admin.carriers.create');
    }

    public function store(StoreCarrierRequest $request): RedirectResponse
    {
        $this->carrierService->create($request->validated());

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier berhasil ditambahkan.');
    }

    public function edit(Carrier $carrier): View
    {
        return view('admin.carriers.edit', compact('carrier'));
    }

    public function update(UpdateCarrierRequest $request, Carrier $carrier): RedirectResponse
    {
        $this->carrierService->update($carrier, $request->validated());

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier berhasil diperbarui.');
    }

    public function destroy(Carrier $carrier): RedirectResponse
    {
        if ($carrier->shipments()->exists()) {
            return back()->withErrors(['carrier' => 'Carrier tidak dapat dihapus karena masih digunakan oleh shipment.']);
        }

        $carrier->delete();

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier berhasil dihapus.');
    }
}

