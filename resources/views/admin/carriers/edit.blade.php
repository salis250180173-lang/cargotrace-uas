@extends('layouts.app')

@section('title', 'Edit Carrier')
@section('page-title', 'Edit Carrier')

@section('content')
    <form method="POST" action="{{ route('admin.carriers.update', $carrier) }}" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name', $carrier->name) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Code *</label>
            <input type="text" name="code" value="{{ old('code', $carrier->code) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Email</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $carrier->contact_email) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Phone</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone', $carrier->contact_phone) }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $carrier->is_active)) class="rounded border-slate-300 text-blue-600">
            <label class="text-xs font-semibold text-slate-700">Active</label>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Update</button>
            <a href="{{ route('admin.carriers.index') }}" class="text-sm text-slate-600">Batal</a>
        </div>
    </form>
@endsection

