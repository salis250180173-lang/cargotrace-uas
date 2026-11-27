@extends('layouts.app')

@section('title', 'Locations')
@section('page-title', 'Locations Management')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Daftar Pelabuhan & Kota Global</h2>
                <p class="text-xs text-slate-500">Kelola lokasi origin dan destination</p>
            </div>
            <a href="{{ route('admin.locations.create') }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                + Add Location
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="px-5 py-3">Code</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Country</th>
                        <th class="px-5 py-3">Coordinates</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($locations as $loc)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 font-bold text-slate-800">{{ $loc->location_code }}</td>
                            <td class="px-5 py-3.5">{{ $loc->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $loc->country }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">{{ $loc->latitude }}, {{ $loc->longitude }}</td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.locations.edit', $loc) }}" class="text-xs font-semibold text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.locations.destroy', $loc) }}" method="POST" class="inline" onsubmit="return confirm('Hapus location ini?')">
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

