@extends('layouts.app')

@section('title', 'Tracking')
@section('page-title', 'Live Tracking Events')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Tracking Feed</h2>
                <p class="text-xs text-slate-500">Riwayat seluruh titik pelacakan pengiriman</p>
            </div>
            <form method="GET" action="{{ route('tracking.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment No / Lokasi..."
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-200">
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Recorded At</th>
                        <th class="px-5 py-3">Shipment No.</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Location</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $ev)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">{{ $ev->recorded_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-3.5 font-semibold">
                                <a href="{{ route('shipments.show', $ev->shipment) }}" class="text-blue-600 hover:underline">
                                    {{ $ev->shipment?->shipment_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-blue-100 text-blue-700">{{ $ev->status }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">{{ $ev->location_name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">{{ $ev->description ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $ev->creator?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada tracking event.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $events->links() }}</div>
        @endif
    </div>
@endsection

