@extends('layouts.app')

@section('title', 'Audit Logs')
@section('page-title', 'System Audit Logs')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Audit Logs</h2>
                <p class="text-xs text-slate-500">Jejak seluruh perubahan data penting di sistem</p>
            </div>
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2">
                <input type="text" name="entity_type" value="{{ request('entity_type') }}" placeholder="Entity (Shipment, User...)"
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-200">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Action (CREATED...)"
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-200">
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Entity</th>
                        <th class="px-5 py-3 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-700">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">{{ $log->action }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-xs font-semibold text-blue-600 hover:underline">Inspect</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection

