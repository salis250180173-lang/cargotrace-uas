@extends('layouts.app')

@section('title', 'Exceptions')
@section('page-title', 'Shipment Exceptions')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Daftar Exceptions</h2>
                <p class="text-xs text-slate-500">Alur penanganan: OPEN &rarr; INVESTIGATING &rarr; RESOLVED</p>
            </div>
            <form method="GET" action="{{ route('exceptions.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                    <option value="">All Status</option>
                    @foreach(['OPEN', 'INVESTIGATING', 'RESOLVED'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
                <select name="severity" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
                    <option value="">All Severity</option>
                    @foreach(['LOW', 'MEDIUM', 'HIGH'] as $sev)
                        <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ $sev }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Shipment No.</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Severity</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Detected At</th>
                        <th class="px-5 py-3">Notes</th>
                        @if(auth()->user()->hasRole(['admin', 'operator']))
                            <th class="px-5 py-3 text-right">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exceptions as $ex)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 font-semibold">
                                <a href="{{ route('shipments.show', $ex->shipment) }}" class="text-blue-600 hover:underline">
                                    {{ $ex->shipment?->shipment_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $ex->exception_type) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $ex->severity === 'HIGH' ? 'bg-rose-600 text-white' : ($ex->severity === 'MEDIUM' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $ex->severity }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $ex->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $ex->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">{{ $ex->detected_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ $ex->notes ?? '-' }}</td>
                            @if(auth()->user()->hasRole(['admin', 'operator']))
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if($ex->status !== 'RESOLVED')
                                        @php $next = $ex->status === 'OPEN' ? 'INVESTIGATING' : 'RESOLVED'; @endphp
                                        <form method="POST" action="{{ route('exceptions.status.update', $ex) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $next }}">
                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 text-white hover:bg-slate-700">
                                                &rarr; {{ $next }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-emerald-600 font-semibold">Done</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada data exception.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exceptions->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $exceptions->links() }}</div>
        @endif
    </div>
@endsection

