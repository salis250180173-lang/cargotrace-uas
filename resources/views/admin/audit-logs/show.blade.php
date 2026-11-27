@extends('layouts.app')

@section('title', 'Audit Log Detail')
@section('page-title', 'Audit Log #' . $auditLog->id)

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 max-w-3xl space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-500">User</p>
                <p class="font-semibold text-slate-800">{{ $auditLog->user?->name ?? 'System' }} ({{ $auditLog->user?->email ?? '-' }})</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Timestamp</p>
                <p class="font-semibold text-slate-800">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Action</p>
                <p class="font-semibold text-slate-800">{{ $auditLog->action }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Entity</p>
                <p class="font-semibold text-slate-800">{{ $auditLog->entity_type }} #{{ $auditLog->entity_id }}</p>
            </div>
        </div>

        <div>
            <p class="text-xs text-slate-500 mb-1">Payload Details (JSON)</p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <a href="{{ route('admin.audit-logs.index') }}" class="inline-block text-sm text-blue-600 hover:underline">&larr; Kembali ke Audit Logs</a>
    </div>
@endsection

