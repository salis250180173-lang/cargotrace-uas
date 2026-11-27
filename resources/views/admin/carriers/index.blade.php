@extends('layouts.app')

@section('title', 'Carriers')
@section('page-title', 'Carriers Management')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Daftar Carriers</h2>
                <p class="text-xs text-slate-500">Kelola mitra ekspedisi global</p>
            </div>
            <a href="{{ route('admin.carriers.create') }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                + Add Carrier
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Code</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($carriers as $c)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 font-bold text-slate-800">{{ $c->code }}</td>
                            <td class="px-5 py-3.5">{{ $c->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $c->contact_email ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $c->contact_phone ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.carriers.edit', $c) }}" class="text-xs font-semibold text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.carriers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Hapus carrier ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

