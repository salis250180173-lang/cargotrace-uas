@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm divide-y divide-slate-100 max-w-3xl">
        @forelse($notifications as $notif)
            <div class="p-4 flex items-start justify-between gap-4 {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/50' }}">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-800">{{ $notif->title }}</p>
                        @if(! $notif->is_read)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-blue-600 text-white">New</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1">{{ $notif->message }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $notif->created_at->format('Y-m-d H:i') }}</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($notif->shipment_id)
                        <a href="{{ route('shipments.show', $notif->shipment_id) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            Lihat Shipment
                        </a>
                    @endif
                    @if(! $notif->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notif) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 text-white hover:bg-slate-700">
                                Mark Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-sm text-slate-400">Belum ada notifikasi.</div>
        @endforelse
    </div>
@endsection

