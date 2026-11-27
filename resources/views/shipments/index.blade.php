@extends('layouts.app')

@section('title', 'Shipments')
@section('page-title', 'Shipments')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Daftar Shipments</h2>
                <p class="text-xs text-slate-500">Kelola dan pantau seluruh pengiriman global</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('shipments.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment No / Customer..."
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

                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-700">Filter</button>
                </form>

                <a href="{{ route('shipments.export.csv', request()->query()) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                    Export CSV
                </a>

                @if(auth()->user()->hasRole(['admin', 'operator']))
                    <a href="{{ route('shipments.create') }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        + New Shipment
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Shipment No.</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Carrier</th>
                        <th class="px-5 py-3">Route</th>
                        <th class="px-5 py-3">Cost</th>
                        <th class="px-5 py-3">ETA</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 font-semibold">
                                <a href="{{ route('shipments.show', $s) }}" class="text-blue-600 hover:underline">{{ $s->shipment_number }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $s->customer?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $s->carrier?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">
                                {{ $s->originLocation?->location_code }} &rarr; {{ $s->destinationLocation?->location_code }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                {{ $s->shipping_cost !== null ? $s->currency_code . ' ' . number_format((float) $s->shipping_cost, 2) : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                {{ ($s->estimated_arrival ?? $s->expected_arrival)?->format('Y-m-d') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-blue-100 text-blue-700">
                                    {{ str_replace('_', ' ', $s->current_status) }}
                                </span>
                                @if($s->is_delayed)
                                    <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-600 text-white">DELAYED</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('shipments.show', $s) }}" class="text-xs font-semibold text-blue-600 hover:underline">Detail</a>
                                @if(auth()->user()->hasRole(['admin', 'operator']))
                                    <a href="{{ route('shipments.edit', $s) }}" class="text-xs font-semibold text-slate-600 hover:underline">Edit</a>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <form action="{{ route('shipments.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Hapus shipment ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada data shipment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $shipments->links() }}</div>
        @endif
    </div>
@endsection

