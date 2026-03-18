<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Hotel Booking')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" integrity="sha256-GzSkJVLJbxDk36qko2cnawOGiqz/Y8GsQv/jMTUrx1Q=" crossorigin="anonymous" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js" integrity="sha256-umbTaFxP31Fv6O1itpLS/3+v5fOAWDLOUzlmvOGaKV4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" integrity="sha256-Huqxy3eUcaCwqqk92RwusapTfWlvAasF6p2rxV6FJaE=" crossorigin="anonymous"></script>
    <style>
        /* Global interactive element standards */
        button, [role="button"], a, summary { cursor: pointer; }
        button:focus-visible, a:focus-visible, [role="button"]:focus-visible,
        input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
            border-radius: 4px;
        }
        label.error { display: block; color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; font-weight: 500; }
        input.error, select.error, textarea.error { border-color: #ef4444 !important; }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col">

<!-- ============================================================
     TOP BAR WITH DROPDOWN
============================================================ -->
<header class="fixed top-0 right-0 z-50 p-4">
    <div class="relative" id="menuDropdownWrapper">
        <button onclick="document.getElementById('menuDropdown').classList.toggle('hidden')"
                class="w-10 h-10 rounded-full bg-white/90 backdrop-blur shadow-lg border border-slate-200/60 flex items-center justify-center hover:bg-white transition group">
            @auth
                <span class="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 group-hover:text-slate-800" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                </svg>
            @endauth
        </button>
        <div id="menuDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 overflow-hidden">
            <a href="{{ route('search.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/></svg>
                Search & Book
            </a>
            @auth
                @if(auth()->user()->is_admin)
                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/></svg>
                    Inventory
                </a>
                <a href="{{ route('discounts.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    Discounts
                </a>
                @endif
                <div class="border-t border-slate-100 my-1"></div>
                <div class="px-4 py-2 text-xs text-slate-400">
                    @if(auth()->user()->is_admin)
                        <span class="inline-block bg-amber-50 text-amber-600 font-semibold px-1.5 py-0.5 rounded mr-1">Admin</span>
                    @endif
                    {{ auth()->user()->name }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H3zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-brand-600 hover:bg-brand-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/></svg>
                    Admin Login
                </a>
            @endauth
        </div>
    </div>
</header>
<script>
    document.addEventListener('click', function(e) {
        const w = document.getElementById('menuDropdownWrapper');
        if (w && !w.contains(e.target)) {
            document.getElementById('menuDropdown').classList.add('hidden');
        }
    });
</script>

<!-- ============================================================
     CONTENT
============================================================ -->
<main class="flex-1">
    @yield('content')
</main>

<!-- ============================================================
     FOOTER
============================================================ -->
<footer class="border-t border-slate-200 py-5 text-center text-slate-400 text-sm bg-white mt-auto">
    &copy; {{ date('Y') }} HotelBook &mdash; Powered by Laravel {{ app()->version() }}
</footer>

@stack('scripts')
</body>
</html>
