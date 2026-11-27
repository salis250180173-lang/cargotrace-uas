<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CargoTrace') — Global Shipment Visibility</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        .leaflet-container { font-family: inherit; z-index: 1; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
<div class="min-h-screen flex">
    {{-- Mobile Backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- Left Sidebar (Dark Navy) --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0f172a] text-slate-300 flex flex-col transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-200 ease-in-out shrink-0">
        {{-- Brand Logo --}}
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <span class="text-white font-bold text-lg tracking-tight">CargoTrace</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto text-sm font-medium">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('*.dashboard', 'dashboard') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('*.dashboard', 'dashboard') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('shipments.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('shipments.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('shipments.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Shipments</span>
            </a>

            <a href="{{ route('tracking.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('tracking.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('tracking.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Tracking</span>
            </a>

            <a href="{{ route('exceptions.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('exceptions.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('exceptions.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Exceptions</span>
            </a>

            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('reports.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Reports</span>
            </a>

            @if(auth()->user()->isAdmin())
                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    Administration
                </div>

                <a href="{{ route('admin.carriers.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.carriers.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.carriers.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Carriers</span>
                </a>

                <a href="{{ route('admin.locations.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.locations.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.locations.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Locations</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Users</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-[#1e293b] text-white' : 'hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.audit-logs.*') ? 'text-blue-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Audit Logs</span>
                </a>
            @endif
        </nav>

        {{-- Bottom Logout --}}
        <div class="p-3 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-rose-500/10 hover:text-rose-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logs Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content Wrapper --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top Light Header --}}
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-xl font-bold text-slate-800 truncate">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Global Search --}}
                <form action="{{ route('shipments.index') }}" method="GET" class="hidden md:flex items-center relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search shipment..."
                           class="pl-9 pr-4 py-1.5 w-56 lg:w-64 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </form>

                {{-- Notification Bell Dropdown --}}
                <div class="relative" id="notif-wrapper">
                    <button type="button" onclick="toggleDropdown('notif-dropdown')" class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if(($headerUnreadCount ?? 0) > 0)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}
                            </span>
                        @endif
                    </button>

                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-semibold text-sm text-slate-800">Notifications</span>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                            @forelse($headerNotifications ?? [] as $notif)
                                <div class="px-4 py-3 {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/60' }} flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 truncate">{{ $notif->title }}</p>
                                        <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if(! $notif->is_read)
                                        <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Mark as read" class="text-[11px] text-blue-600 hover:underline shrink-0">Read</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-slate-500">
                                    Belum ada notifikasi.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User Profile + Role Badge --}}
                @php
                    $user = auth()->user();
                    $initials = collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                    $roleColors = [
                        'admin' => 'bg-purple-100 text-purple-700',
                        'operator' => 'bg-blue-100 text-blue-700',
                        'customer' => 'bg-emerald-100 text-emerald-700',
                    ];
                @endphp
                <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                        <span class="inline-block text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ $roleColors[$user->role] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $user->role }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6 space-y-5">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
                    <p class="font-semibold mb-1">Terjadi kesalahan validasi:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    }

    function toggleDropdown(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('notif-wrapper');
        const dropdown = document.getElementById('notif-dropdown');
        if (wrapper && dropdown && !wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
@stack('scripts')
</body>
</html>

