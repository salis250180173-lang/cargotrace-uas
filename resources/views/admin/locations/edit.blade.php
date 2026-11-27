@extends('layouts.app')

@section('title', 'Edit Location')
@section('page-title', 'Edit Location')

@section('content')
    <form method="POST" action="{{ route('admin.locations.update', $location) }}" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Port / City Name *</label>
            <input type="text" name="name" value="{{ old('name', $location->name) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Country *</label>
            <input type="text" name="country" value="{{ old('country', $location->country) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Location Code *</label>
            <input type="text" name="location_code" value="{{ old('location_code', $location->location_code) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $location->latitude) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $location->longitude) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
            </div>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Update</button>
            <a href="{{ route('admin.locations.index') }}" class="text-sm text-slate-600">Batal</a>
        </div>
    </form>
@endsection

