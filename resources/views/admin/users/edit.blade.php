@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Role *</label>
            <select name="role" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-white">
                <option value="customer" @selected(old('role', $user->role) === 'customer')>Customer</option>
                <option value="operator" @selected(old('role', $user->role) === 'operator')>Operator</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">New Password (opsional)</label>
            <input type="password" name="password" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300">
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Update</button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600">Batal</a>
        </div>
    </form>
@endsection

