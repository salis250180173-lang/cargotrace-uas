<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0f172a] flex items-center justify-center p-4">
<div class="w-full max-w-md space-y-6">
    {{-- Brand Header --}}
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/30 mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">CargoTrace</h1>
        <p class="text-sm text-slate-400 mt-1">Global Shipment Visibility & Tracking Platform</p>
    </div>

    {{-- Login Card --}}
    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Masuk ke Akun Anda</h2>
        <p class="text-xs text-slate-500 mb-5">Silakan masukkan email dan password yang terdaftar.</p>

        @if($errors->any())
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-3.5 py-2.5 rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="name@cargotrace.test"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                Sign In
            </button>
        </form>
    </div>

    {{-- Demo Accounts Helper Card --}}
    <div class="bg-[#1e293b] border border-slate-700/80 rounded-2xl p-4 text-xs text-slate-300">
        <p class="font-semibold text-white mb-2 flex items-center justify-between">
            <span>Akun Demo (Klik untuk isi otomatis)</span>
            <span class="text-[10px] text-slate-400 font-normal">Password: <code class="text-blue-300">password</code></span>
        </p>
        <div class="grid grid-cols-1 gap-2">
            <button type="button" onclick="fillDemo('admin@cargotrace.test')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 transition text-left">
                <span><strong class="text-purple-300">Admin:</strong> admin@cargotrace.test</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300">Full Access</span>
            </button>
            <button type="button" onclick="fillDemo('operator@cargotrace.test')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 transition text-left">
                <span><strong class="text-blue-300">Operator:</strong> operator@cargotrace.test</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300">Operations</span>
            </button>
            <button type="button" onclick="fillDemo('customer@cargotrace.test')"
                    class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 transition text-left">
                <span><strong class="text-emerald-300">Customer:</strong> customer@cargotrace.test</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">My Shipments</span>
            </button>
        </div>
    </div>
</div>

<script>
    function fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
    }
</script>
</body>
</html>

