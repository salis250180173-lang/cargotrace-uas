<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Requests\UpdateShipmentStatusRequest;
use App\Models\Shipment;
use App\Models\User;
use App\Services\CarrierService;
use App\Services\ExternalApiService;
use App\Services\LocationService;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected CarrierService $carrierService,
        protected LocationService $locationService,
        protected ExternalApiService $externalApiService
    ) {}

    public function index(Request $request): View
    {
        $query = $this->shipmentService->queryForUser($request->user());

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('carrier', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'DELAYED') {
                $query->where('is_delayed', true);
            } else {
                $query->where('current_status', $request->input('status'));
            }
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        $shipments = $query->paginate(15)->withQueryString();
        $carriers = $this->carrierService->getActive();

        return view('shipments.index', compact('shipments', 'carriers'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->shipmentService->queryForUser($request->user());

        if ($request->filled('status')) {
            if ($request->input('status') === 'DELAYED') {
                $query->where('is_delayed', true);
            } else {
                $query->where('current_status', $request->input('status'));
            }
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        $shipments = $query->get();

        return response()->streamDownload(function () use ($shipments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Shipment No',
                'Customer',
                'Carrier',
                'Origin',
                'Destination',
                'Cargo Type',
                'Weight (kg)',
                'Shipping Cost',
                'Currency',
                'Expected Arrival',
                'Estimated Arrival',
                'Status',
                'Is Delayed',
                'Delay Severity',
            ]);

            foreach ($shipments as $s) {
                fputcsv($handle, [
                    $s->shipment_number,
                    $s->customer?->name,
                    $s->carrier?->name,
                    $s->originLocation?->name,
                    $s->destinationLocation?->name,
                    $s->cargo_type,
                    $s->weight,
                    $s->shipping_cost,
                    $s->currency_code,
                    $s->expected_arrival?->format('Y-m-d H:i'),
                    $s->estimated_arrival?->format('Y-m-d H:i'),
                    $s->current_status,
                    $s->is_delayed ? 'YES' : 'NO',
                    $s->delay_severity,
                ]);
            }

            fclose($handle);
        }, 'cargotrace-shipments-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Shipment::class);

        return view('shipments.create', [
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(),
            'carriers' => $this->carrierService->getActive(),
            'locations' => $this->locationService->getAll(),
            'currencies' => config('cargotrace.exchangerate.supported_currencies', ['USD', 'EUR', 'SGD', 'AUD', 'IDR']),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $shipment = $this->shipmentService->create($validated, $items);

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} berhasil dibuat.");
    }

    public function show(Request $request, Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents.creator',
            'exceptions',
        ]);

        $latestEvent = $shipment->trackingEvents->last();
        $weatherLat = $latestEvent?->latitude ?? $shipment->destinationLocation?->latitude;
        $weatherLon = $latestEvent?->longitude ?? $shipment->destinationLocation?->longitude;
        $weatherLocation = $latestEvent?->location_name ?? $shipment->destinationLocation?->name;

        $weather = $this->externalApiService->getWeather(
            $weatherLat !== null ? (float) $weatherLat : null,
            $weatherLon !== null ? (float) $weatherLon : null,
            $weatherLocation
        );

        $targetCurrency = $request->query('currency');
        $convertedCost = null;
        if ($targetCurrency && $shipment->shipping_cost !== null) {
            $convertedCost = $this->externalApiService->convertCurrency(
                (float) $shipment->shipping_cost,
                $shipment->currency_code,
                $targetCurrency
            );
        }

        $supportedCurrencies = config('cargotrace.exchangerate.supported_currencies', ['USD', 'EUR', 'SGD', 'AUD', 'IDR']);

        return view('shipments.show', compact(
            'shipment',
            'weather',
            'convertedCost',
            'targetCurrency',
            'supportedCurrencies'
        ));
    }

    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        return view('shipments.edit', [
            'shipment' => $shipment->load('items'),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->orderBy('name')->get(),
            'carriers' => $this->carrierService->getActive(),
            'locations' => $this->locationService->getAll(),
            'currencies' => config('cargotrace.exchangerate.supported_currencies', ['USD', 'EUR', 'SGD', 'AUD', 'IDR']),
        ]);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->shipmentService->update($shipment, $request->validated());

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} berhasil diperbarui.");
    }

    public function updateStatus(UpdateShipmentStatusRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $this->shipmentService->updateStatus($shipment, $request->validated('current_status'));

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', "Status shipment {$shipment->shipment_number} berhasil diperbarui.");
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->delete($shipment);

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Shipment berhasil dihapus.');
    }
}
