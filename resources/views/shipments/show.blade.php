@extends('layouts.app')

@section('title', $shipment->shipment_number)
@section('page-title', 'Shipment ' . $shipment->shipment_number)

@section('content')
    @php
        $nextStatusMap = [
            'CREATED' => 'BOOKED',
            'BOOKED' => 'PICKED_UP',
            'PICKED_UP' => 'IN_TRANSIT',
            'IN_TRANSIT' => 'DELIVERED',
            'DELIVERED' => null,
        ];
        $nextStatus = $nextStatusMap[$shipment->current_status] ?? null;
    @endphp

    {{-- Top Summary Card --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">{{ $shipment->shipment_number }}</h2>
                <span class="px-2.5 py-1 rounded text-xs font-bold uppercase bg-blue-600 text-white">
                    {{ str_replace('_', ' ', $shipment->current_status) }}
                </span>
                @if($shipment->is_delayed)
                    <span class="px-2.5 py-1 rounded text-xs font-bold uppercase bg-rose-600 text-white">
                        DELAYED ({{ $shipment->delay_hours }}h - {{ $shipment->delay_severity }})
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500">
                Customer: <strong>{{ $shipment->customer?->name }}</strong> &bull;
                Carrier: <strong>{{ $shipment->carrier?->name }}</strong> &bull;
                Route: <strong>{{ $shipment->originLocation?->name }}</strong> &rarr; <strong>{{ $shipment->destinationLocation?->name }}</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->hasRole(['admin', 'operator']))
                @if($nextStatus)
                    <form method="POST" action="{{ route('shipments.status.update', $shipment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="current_status" value="{{ $nextStatus }}">
                        <button type="submit" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition">
                            Advance to {{ str_replace('_', ' ', $nextStatus) }} &rarr;
                        </button>
                    </form>
                @endif
                <a href="{{ route('shipments.edit', $shipment) }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-200 bg-white hover:bg-slate-50">
                    Edit Shipment
                </a>
            @endif
        </div>
    </div>

    {{-- Map + Weather + Currency Conversion --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Interactive Leaflet Map --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 text-sm mb-3">Interactive Route & Tracking Map</h3>
            <div id="shipmentDetailMap" class="w-full h-80 rounded-lg border border-slate-200"></div>
        </div>

        {{-- Right Info: Weather, Cost & Currency Converter, Items --}}
        <div class="space-y-5">
            {{-- OpenWeatherMap Card --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 text-sm mb-2">Cuaca Lokasi Saat Ini / Tujuan</h3>
                @if($weather['available'] ?? false)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $weather['location'] }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ $weather['description'] }}</p>
                            <p class="text-xs text-slate-500 mt-1">Humidity: {{ $weather['humidity'] }}% &bull; Wind: {{ $weather['wind_speed'] }} m/s</p>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">{{ $weather['temperature'] }}&deg;C</div>
                    </div>
                @else
                    <p class="text-xs text-slate-500">{{ $weather['message'] ?? 'Data cuaca tidak tersedia.' }}</p>
                @endif
            </div>

            {{-- Shipping Cost & ExchangeRate Converter --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <h3 class="font-bold text-slate-800 text-sm">Shipping Cost & Currency</h3>
                <p class="text-xl font-bold text-slate-800">
                    {{ $shipment->shipping_cost !== null ? $shipment->currency_code . ' ' . number_format((float) $shipment->shipping_cost, 2) : '-' }}
                </p>

                @if($shipment->shipping_cost !== null)
                    <form method="GET" action="{{ route('shipments.show', $shipment) }}" class="flex items-center gap-2">
                        <select name="currency" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                            @foreach($supportedCurrencies as $cur)
                                <option value="{{ $cur }}" @selected(($targetCurrency ?? $shipment->currency_code) === $cur)>{{ $cur }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-700">
                            Convert
                        </button>
                    </form>

                    @if($convertedCost)
                        @if($convertedCost['available'])
                            <div class="p-2.5 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-800">
                                <strong>{{ $convertedCost['to'] }} {{ number_format((float) $convertedCost['converted_amount'], 2) }}</strong>
                                <span class="text-blue-600">(Rate: {{ $convertedCost['rate'] }})</span>
                            </div>
                        @else
                            <p class="text-xs text-amber-600">{{ $convertedCost['message'] }}</p>
                        @endif
                    @endif
                @endif
            </div>

            {{-- Cargo Items --}}
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 text-sm mb-2">Cargo Items ({{ $shipment->items->count() }})</h3>
                <ul class="divide-y divide-slate-100 text-xs">
                    @forelse($shipment->items as $item)
                        <li class="py-2 flex justify-between">
                            <span class="font-medium text-slate-700">{{ $item->item_name }}</span>
                            <span class="text-slate-500">{{ $item->quantity }}x ({{ $item->unit_weight ?? 0 }} kg)</span>
                        </li>
                    @empty
                        <li class="py-2 text-slate-400">Tidak ada item tercatat.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Timeline + Operator Forms --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Vertical Chronological Timeline (Events + Exceptions) --}}
        @php
            $timeline = collect();
            foreach ($shipment->trackingEvents as $ev) {
                $timeline->push([
                    'type' => 'event',
                    'time' => $ev->recorded_at,
                    'data' => $ev,
                ]);
            }
            foreach ($shipment->exceptions as $ex) {
                $timeline->push([
                    'type' => 'exception',
                    'time' => $ex->detected_at,
                    'data' => $ex,
                ]);
            }
            $timeline = $timeline->sortByDesc('time');
        @endphp

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 text-base mb-4">Chronological Timeline</h3>

            @if($timeline->isEmpty())
                <p class="text-xs text-slate-400 py-6 text-center">Belum ada tracking event atau exception.</p>
            @else
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-6">
                    @foreach($timeline as $entry)
                        @if($entry['type'] === 'event')
                            @php $ev = $entry['data']; @endphp
                            <div class="relative">
                                <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow"></span>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-bold uppercase text-blue-700 bg-blue-50 px-2 py-0.5 rounded">{{ str_replace('_', ' ', $ev->status) }}</span>
                                    <span class="text-xs text-slate-400">{{ $ev->recorded_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <p class="text-sm font-semibold text-slate-800 mt-1">{{ $ev->location_name }}</p>
                                @if($ev->description)
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $ev->description }}</p>
                                @endif
                            </div>
                        @else
                            @php $ex = $entry['data']; @endphp
                            <div class="relative bg-rose-50/70 border border-rose-200 rounded-lg p-3">
                                <span class="absolute -left-[35px] top-3 w-4 h-4 rounded-full bg-rose-600 border-2 border-white shadow"></span>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold uppercase text-rose-700">{{ str_replace('_', ' ', $ex->exception_type) }}</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-600 text-white">{{ $ex->severity }}</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-800 text-white">{{ $ex->status }}</span>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ $ex->detected_at->format('Y-m-d H:i') }}</span>
                                </div>
                                @if($ex->notes)
                                    <p class="text-xs text-slate-700 mt-1">{{ $ex->notes }}</p>
                                @endif

                                @if(auth()->user()->hasRole(['admin', 'operator']) && $ex->status !== 'RESOLVED')
                                    @php $nextEx = $ex->status === 'OPEN' ? 'INVESTIGATING' : 'RESOLVED'; @endphp
                                    <form method="POST" action="{{ route('exceptions.status.update', $ex) }}" class="mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $nextEx }}">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-slate-800 text-white hover:bg-slate-700">
                                            Mark as {{ $nextEx }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Operator/Admin Action Forms --}}
        @if(auth()->user()->hasRole(['admin', 'operator']))
            <div class="space-y-5">
                {{-- Add Tracking Event --}}
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
                    <h3 class="font-bold text-slate-800 text-sm mb-3">Tambah Tracking Event</h3>
                    <form method="POST" action="{{ route('shipments.tracking-events.store', $shipment) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status *</label>
                            <select name="status" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white">
                                @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'] as $st)
                                    <option value="{{ $st }}" @selected($st === ($nextStatus ?? $shipment->current_status))>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Location Name *</label>
                            <input type="text" name="location_name" required placeholder="e.g. Port of Singapore"
                                   class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                                <input type="number" step="0.0000001" name="latitude" placeholder="1.2644"
                                       class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                                <input type="number" step="0.0000001" name="longitude" placeholder="103.8222"
                                       class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Recorded At</label>
                            <input type="datetime-local" name="recorded_at" value="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                            Simpan Tracking Event
                        </button>
                    </form>
                </div>

                {{-- Report Exception --}}
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5">
                    <h3 class="font-bold text-slate-800 text-sm mb-3">Laporkan Exception</h3>
                    <form method="POST" action="{{ route('shipments.exceptions.store', $shipment) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Exception Type *</label>
                            <select name="exception_type" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white">
                                @foreach(['CUSTOMS_HOLD', 'WEATHER_DELAY', 'PORT_CONGESTION', 'DAMAGED', 'MISROUTED', 'DOCUMENTATION_ISSUE'] as $et)
                                    <option value="{{ $et }}">{{ str_replace('_', ' ', $et) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Severity *</label>
                            <select name="severity" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white">
                                <option value="LOW">LOW</option>
                                <option value="MEDIUM">MEDIUM</option>
                                <option value="HIGH">HIGH</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white">
                            Report Exception
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    const detailMap = L.map('shipmentDetailMap').setView([0, 100], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(detailMap);

    const routePoints = [];

    @if($shipment->originLocation && $shipment->originLocation->latitude && $shipment->originLocation->longitude)
        const originCoords = [{{ $shipment->originLocation->latitude }}, {{ $shipment->originLocation->longitude }}];
        routePoints.push(originCoords);
        L.marker(originCoords).addTo(detailMap)
            .bindPopup('<strong>Origin:</strong> {{ e($shipment->originLocation->name) }} ({{ $shipment->originLocation->location_code }})');
    @endif

    @foreach($shipment->trackingEvents as $ev)
        @if($ev->latitude && $ev->longitude)
            const evCoords{{ $ev->id }} = [{{ $ev->latitude }}, {{ $ev->longitude }}];
            routePoints.push(evCoords{{ $ev->id }});
            L.circleMarker(evCoords{{ $ev->id }}, {
                radius: 7,
                color: '#1d4ed8',
                fillColor: '#3b82f6',
                fillOpacity: 0.9
            }).addTo(detailMap).bindPopup(
                '<strong>{{ e($ev->location_name) }}</strong><br>Status: {{ $ev->status }}<br>{{ $ev->recorded_at->format("Y-m-d H:i") }}'
            );
        @endif
    @endforeach

    @if($shipment->destinationLocation && $shipment->destinationLocation->latitude && $shipment->destinationLocation->longitude)
        const destCoords = [{{ $shipment->destinationLocation->latitude }}, {{ $shipment->destinationLocation->longitude }}];
        routePoints.push(destCoords);
        L.marker(destCoords).addTo(detailMap)
            .bindPopup('<strong>Destination:</strong> {{ e($shipment->destinationLocation->name) }} ({{ $shipment->destinationLocation->location_code }})');
    @endif

    if (routePoints.length >= 2) {
        L.polyline(routePoints, { color: '#2563eb', weight: 3 }).addTo(detailMap);
    }
    if (routePoints.length > 0) {
        detailMap.fitBounds(routePoints, { padding: [35, 35], maxZoom: 6 });
    }
</script>
@endpush

