@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Shipment Analytics & Reports')

@section('content')
    <div class="flex justify-end">
        <a href="{{ route('shipments.export.csv') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
            Export Full Shipments CSV
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5">
            <p class="text-xs text-slate-500 font-medium">Total Shipments</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalShipments) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-5">
            <p class="text-xs text-slate-500 font-medium">Delivered</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($deliveredCount) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-5">
            <p class="text-xs text-slate-500 font-medium">Delayed Shipments</p>
            <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($delayedCount) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/80 p-5">
            <p class="text-xs text-slate-500 font-medium">On-Time Performance</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $onTimeRate }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Carrier Performance Table --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 text-base">Carrier Performance</h2>
            </div>
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Carrier</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Delivered</th>
                        <th class="px-5 py-3">Delayed</th>
                        <th class="px-5 py-3">Delay Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($carrierPerformance as $cp)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $cp['name'] }} ({{ $cp['code'] }})</td>
                            <td class="px-5 py-3">{{ $cp['total'] }}</td>
                            <td class="px-5 py-3 text-emerald-600 font-semibold">{{ $cp['delivered'] }}</td>
                            <td class="px-5 py-3 text-rose-600 font-semibold">{{ $cp['delayed'] }}</td>
                            <td class="px-5 py-3">{{ $cp['delay_rate'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Status & Exception Breakdown --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-5 space-y-5">
            <div>
                <h2 class="font-bold text-slate-800 text-base mb-3">Shipments by Stage</h2>
                <div class="space-y-2 text-sm">
                    @foreach($statusBreakdown as $st => $cnt)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <span class="font-medium text-slate-600">{{ str_replace('_', ' ', $st) }}</span>
                            <span class="font-bold text-slate-800">{{ $cnt }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="font-bold text-slate-800 text-base mb-3">Exceptions by Type</h2>
                <div class="space-y-2 text-sm">
                    @forelse($exceptionSummary as $type => $cnt)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <span class="font-medium text-slate-600">{{ str_replace('_', ' ', $type) }}</span>
                            <span class="font-bold text-rose-600">{{ $cnt }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada exception tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

