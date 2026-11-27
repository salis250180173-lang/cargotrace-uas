@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Row 1: 5 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Shipments --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Total Shipments</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($totalShipments) }}</p>
            </div>
        </div>

        {{-- In Transit --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">In Transit</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($inTransitCount) }}</p>
            </div>
        </div>

        {{-- Delivered --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Delivered</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($deliveredCount) }}</p>
            </div>
        </div>

        {{-- Delayed --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Delayed</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($delayedCount) }}</p>
            </div>
        </div>

        {{-- Open Exceptions --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-4 flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Open Exceptions</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($openExceptionsCount) }}</p>
            </div>
        </div>
    </div>

    {{-- Row 2: 3 Columns (Bar Chart, Doughnut Chart, Mini Map) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Bar Chart: Shipments by Status --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 flex flex-col">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="font-bold text-slate-800 text-base">Shipments by Status</h2>
                    <p class="text-xs text-slate-500">Overview of shipments by status</p>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-lg px-2.5 py-1 text-right">
                    <p class="text-sm font-bold text-blue-700 leading-none">{{ number_format($totalShipments) }}</p>
                    <p class="text-[10px] text-blue-600">Total</p>
                </div>
            </div>
            <div class="relative flex-1 min-h-[210px]">
                <canvas id="statusBarChart"></canvas>
            </div>
        </div>

        {{-- Doughnut Chart: Delay Rate by Carrier --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 flex flex-col">
            <div class="mb-4">
                <h2 class="font-bold text-slate-800 text-base">Delay Rate by Carrier</h2>
                <p class="text-xs text-slate-500">Distribution of delay rates across carriers</p>
            </div>
            <div class="flex items-center gap-4 flex-1">
                <div class="relative w-44 h-44 mx-auto shrink-0">
                    <canvas id="carrierDoughnutChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-bold text-slate-800">{{ $averageDelayRate }}%</span>
                        <span class="text-[10px] text-slate-500 text-center leading-tight">Average Delay<br>Rate</span>
                    </div>
                </div>
                <div class="flex-1 space-y-2 text-xs min-w-0">
                    @php $dotColors = ['bg-blue-600', 'bg-amber-500', 'bg-slate-500', 'bg-emerald-500', 'bg-indigo-500']; @endphp
                    @forelse($carrierStats as $idx => $cs)
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 truncate text-slate-600">
                                <span class="w-2.5 h-2.5 rounded-sm {{ $dotColors[$idx % count($dotColors)] }} shrink-0"></span>
                                <span class="truncate">{{ $cs['name'] }}</span>
                            </span>
                            <span class="font-semibold text-slate-800 shrink-0">{{ $cs['rate'] }}%</span>
                        </div>
                    @empty
                        <p class="text-slate-400">No carrier data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Mini Map: Shipment Map --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 flex flex-col">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-800 text-base">Shipment Map</h2>
                    <p class="text-xs text-slate-500">Real-time shipment routes and locations</p>
                </div>
                <a href="{{ route('tracking.index') }}" class="text-xs font-medium text-blue-600 hover:underline">Full View</a>
            </div>
            <div id="dashboardMap" class="w-full h-52 rounded-lg border border-slate-200 overflow-hidden"></div>
        </div>
    </div>

    {{-- Row 3: Recent Shipments Table --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Recent Shipments</h2>
                <p class="text-xs text-slate-500">Live overview of all shipments</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <select name="status" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                        <option value="">All Status</option>
                        @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'DELAYED'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ str_replace('_', ' ', $st) }}</option>
                        @endforeach
                    </select>

                    <select name="carrier_id" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                        <option value="">All Carriers</option>
                        @foreach($carriers as $c)
                            <option value="{{ $c->id }}" @selected((string) request('carrier_id') === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>

                    <select name="origin_id" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                        <option value="">All Origins</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected((string) request('origin_id') === (string) $loc->id)>{{ $loc->location_code }} - {{ $loc->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-700 transition">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'status', 'carrier_id', 'origin_id', 'destination_id']))
                        <a href="{{ url()->current() }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800">Reset</a>
                    @endif
                </form>

                <a href="{{ route('shipments.export.csv', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>

                @if(auth()->user()->hasRole(['admin', 'operator']))
                    <a href="{{ route('shipments.create') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                        + New Shipment
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'shipment_number', 'sort_dir' => $sortBy === 'shipment_number' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-slate-800">
                                Shipment No.
                            </a>
                        </th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Carrier</th>
                        <th class="px-5 py-3">Origin</th>
                        <th class="px-5 py-3">Destination</th>
                        <th class="px-5 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'expected_arrival', 'sort_dir' => $sortBy === 'expected_arrival' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-slate-800">
                                ETA
                            </a>
                        </th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentShipments as $s)
                        @php
                            $statusBadges = [
                                'CREATED' => 'bg-slate-100 text-slate-700',
                                'BOOKED' => 'bg-indigo-100 text-indigo-700',
                                'PICKED_UP' => 'bg-cyan-100 text-cyan-700',
                                'IN_TRANSIT' => 'bg-blue-600 text-white',
                                'DELIVERED' => 'bg-emerald-600 text-white',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-semibold">
                                <a href="{{ route('shipments.show', $s) }}" class="text-blue-600 hover:underline">
                                    {{ $s->shipment_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $s->customer?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $s->carrier?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $s->originLocation?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $s->destinationLocation?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                {{ ($s->estimated_arrival ?? $s->expected_arrival)?->format('Y-m-d') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase {{ $statusBadges[$s->current_status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ str_replace('_', ' ', $s->current_status) }}
                                    </span>
                                    @if($s->is_delayed)
                                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-rose-600 text-white">
                                            DELAYED
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-sm">
                                Belum ada data shipment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentShipments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $recentShipments->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // 1. Bar Chart: Shipments by Status
    const statusCtx = document.getElementById('statusBarChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: @json($statusChartData['labels']),
            datasets: [{
                label: 'Shipments',
                data: @json($statusChartData['values']),
                backgroundColor: ['#2563eb', '#10b981', '#ea580c', '#dc2626'],
                borderRadius: 6,
                barThickness: 32,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Doughnut Chart: Delay Rate by Carrier
    const carrierStats = @json($carrierStats);
    const doughnutCtx = document.getElementById('carrierDoughnutChart').getContext('2d');
    const hasCarrierRates = carrierStats.some(c => c.rate > 0);
    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: carrierStats.map(c => c.name),
            datasets: [{
                data: hasCarrierRates ? carrierStats.map(c => c.rate) : carrierStats.map(() => 1),
                backgroundColor: ['#2563eb', '#f97316', '#64748b', '#10b981', '#6366f1'],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${carrierStats[ctx.dataIndex].rate}%`
                    }
                }
            }
        }
    });

    // 3. Mini Map (Leaflet.js)
    const map = L.map('dashboardMap').setView([10, 80], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const mapShipments = @json($mapShipments);
    const bounds = [];

    mapShipments.forEach(s => {
        const points = [];
        if (s.origin && s.origin.lat && s.origin.lng) {
            points.push([s.origin.lat, s.origin.lng]);
            bounds.push([s.origin.lat, s.origin.lng]);
        }
        if (s.current && s.current.lat && s.current.lng) {
            points.push([s.current.lat, s.current.lng]);
            bounds.push([s.current.lat, s.current.lng]);
            L.circleMarker([s.current.lat, s.current.lng], {
                radius: 7,
                color: s.is_delayed ? '#dc2626' : '#2563eb',
                fillColor: s.is_delayed ? '#ef4444' : '#3b82f6',
                fillOpacity: 0.9
            }).addTo(map).bindPopup(
                `<strong>${s.shipment_number}</strong><br>Carrier: ${s.carrier}<br>Status: ${s.status}<br><a href="${s.url}" style="color:#2563eb;">Detail &rarr;</a>`
            );
        }
        if (s.destination && s.destination.lat && s.destination.lng) {
            points.push([s.destination.lat, s.destination.lng]);
            bounds.push([s.destination.lat, s.destination.lng]);
        }
        if (points.length >= 2) {
            L.polyline(points, {
                color: s.is_delayed ? '#dc2626' : '#2563eb',
                weight: 2.5,
                dashArray: '5, 5'
            }).addTo(map);
        } else if (points.length === 1) {
            L.marker(points[0]).addTo(map).bindPopup(`<strong>${s.shipment_number}</strong> (${s.status})`);
        }
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [25, 25], maxZoom: 5 });
    }
</script>
@endpush

