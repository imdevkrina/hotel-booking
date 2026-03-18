@extends('layouts.app')

@section('title', 'Hotel Booking — Find Your Perfect Stay')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(165deg, #0f172a 0%, #1e3a5f 35%, #1a4a7a 60%, #0c4a6e 100%);
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: linear-gradient(to top, #f8fafc, transparent);
    }
    .search-panel {
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(20px);
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.1);
    }
    .field-group { position: relative; }
    .field-group .field-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; pointer-events: none; transition: color 0.15s; z-index: 2;
    }
    .field-group:focus-within .field-icon,
    .field-group.dropdown-open .field-icon { color: #2563eb; }
    .field-group input, .field-group select {
        padding-left: 42px;
    }
    .field-divider {
        position: absolute; right: 0; top: 20%; height: 60%;
        width: 1px; background: #e2e8f0;
    }
    /* Custom dropdown trigger */
    .custom-select-trigger {
        padding-left: 42px;
        cursor: pointer; user-select: none;
    }
    .custom-select-trigger .chevron-icon {
        transition: transform 0.2s;
    }
    .dropdown-open .custom-select-trigger .chevron-icon {
        transform: rotate(180deg);
    }
    /* Custom dropdown panel */
    .custom-dropdown {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: white; border-radius: 12px;
        box-shadow: 0 12px 40px -8px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
        z-index: 9999; overflow: hidden;
        opacity: 0; transform: translateY(-4px); pointer-events: none;
        transition: opacity 0.15s, transform 0.15s;
    }
    .dropdown-open .custom-dropdown {
        opacity: 1; transform: translateY(0); pointer-events: auto;
    }
    .custom-dropdown-item {
        padding: 10px 16px; cursor: pointer; display: flex; align-items: center; gap: 10px;
        font-size: 0.8125rem; font-weight: 500; color: #334155;
        transition: background 0.1s;
    }
    .custom-dropdown-item:hover { background: #f1f5f9; }
    .custom-dropdown-item.active { background: #eff6ff; color: #2563eb; }
    .custom-dropdown-item .item-icon { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.6; }
    .custom-dropdown-item.active .item-icon { opacity: 1; }
    .custom-dropdown-item .check-mark { margin-left: auto; opacity: 0; }
    .custom-dropdown-item.active .check-mark { opacity: 1; }
    /* Flatpickr theme overrides */
    .flatpickr-calendar {
        border-radius: 14px !important;
        box-shadow: 0 16px 48px -8px rgba(0,0,0,0.18), 0 0 0 1px rgba(0,0,0,0.05) !important;
        border: 1px solid #e2e8f0 !important;
        font-family: 'Inter', sans-serif !important;
        padding: 16px !important;
        z-index: 9999 !important;
        width: 320px !important;
    }
    .flatpickr-innerContainer {
        padding: 4px 0 !important;
    }
    .flatpickr-months {
        padding: 4px 4px 0 !important;
    }
    .flatpickr-months .flatpickr-month {
        height: 44px !important;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        padding: 8px !important;
        border-radius: 8px !important;
        top: 8px !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
        background: #f1f5f9 !important;
    }
    .flatpickr-months .flatpickr-prev-month svg,
    .flatpickr-months .flatpickr-next-month svg {
        width: 12px !important;
        height: 12px !important;
    }
    .flatpickr-current-month {
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        padding-top: 6px !important;
    }
    .flatpickr-weekdays {
        padding: 0 4px !important;
    }
    span.flatpickr-weekday {
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }
    .flatpickr-days {
        padding: 0 2px !important;
    }
    .dayContainer {
        min-width: 280px !important;
        max-width: 280px !important;
        padding: 2px 0 !important;
    }
    .flatpickr-day {
        border-radius: 8px !important;
        font-weight: 500 !important;
        font-size: 0.8125rem !important;
        height: 36px !important;
        line-height: 36px !important;
        margin: 1px !important;
        max-width: 38px !important;
    }
    .flatpickr-day.selected {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
        font-weight: 600 !important;
    }
    .flatpickr-day.today:not(.selected) {
        border-color: #93c5fd !important;
        background: #eff6ff !important;
    }
    .flatpickr-day:hover:not(.selected):not(.flatpickr-disabled) {
        background: #eff6ff !important;
        border-color: #dbeafe !important;
    }
    .flatpickr-day.flatpickr-disabled {
        color: #cbd5e1 !important;
    }
    .result-card { transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s cubic-bezier(.4,0,.2,1); }
    .result-card:hover { transform: translateY(-6px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); }
    .spinner { width: 44px; height: 44px; border: 4px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fadeInUp 0.6s ease-out both; }
    .animate-delay-1 { animation-delay: 0.1s; }
    .animate-delay-2 { animation-delay: 0.2s; }
    .animate-delay-3 { animation-delay: 0.3s; }
</style>
@endpush

@section('content')

<!-- ============================================================
     HERO SECTION
============================================================ -->
<section class="hero-section flex flex-col items-center justify-center px-4 pt-16 pb-28 relative z-10">
    <div class="relative z-20 w-full max-w-5xl text-center mb-8 animate-fade-in-up">
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-3.5 py-1 mb-4">
            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
            <span class="text-blue-100 text-[11px] font-semibold tracking-wider uppercase">Best Rate Guaranteed</span>
        </div>
        <h1 class="text-white text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold leading-tight tracking-tight">
            Find Your <span class="bg-gradient-to-r from-blue-200 via-cyan-200 to-blue-200 bg-clip-text text-transparent">Perfect Stay</span>
        </h1>
        <p class="mt-2.5 text-blue-200/70 text-sm sm:text-base max-w-lg mx-auto font-light">
            Luxury rooms at unbeatable prices &mdash; check availability instantly.
        </p>
    </div>

    <!-- ============================================================
         SEARCH PANEL
    ============================================================ -->
    <div class="relative z-20 w-full max-w-5xl animate-fade-in-up animate-delay-2">
        <div class="search-panel rounded-2xl lg:rounded-full px-3 py-3 lg:px-4">
            <form id="searchForm" novalidate class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-0">

                <!-- Check-in -->
                <div class="field-group flex-1 relative">
                    <svg class="field-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" id="check_in_date" name="check_in_date" readonly
                           placeholder="Check-in"
                           class="w-full bg-transparent border-0 rounded-xl lg:rounded-full py-3.5 text-slate-800 text-sm font-medium focus:outline-none focus:bg-blue-50/50 transition placeholder:text-slate-400 cursor-pointer"
                           style="padding-left:42px" />
                    <span class="hidden lg:block field-divider"></span>
                </div>

                <!-- Check-out -->
                <div class="field-group flex-1 relative">
                    <svg class="field-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" id="check_out_date" name="check_out_date" readonly
                           placeholder="Check-out"
                           class="w-full bg-transparent border-0 rounded-xl lg:rounded-full py-3.5 text-slate-800 text-sm font-medium focus:outline-none focus:bg-blue-50/50 transition placeholder:text-slate-400 cursor-pointer"
                           style="padding-left:42px" />
                    <span class="hidden lg:block field-divider"></span>
                </div>

                <!-- Guests (Custom Dropdown) -->
                <div class="field-group flex-1 relative" id="guestDropdownWrap">
                    <svg class="field-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <input type="hidden" id="guest_count" name="guest_count" value="2" />
                    <div class="custom-select-trigger w-full rounded-xl lg:rounded-full py-3.5 text-slate-800 text-sm font-medium flex items-center justify-between pr-3" onclick="toggleDropdown('guest')">
                        <span id="guestLabel">2 Guests</span>
                        <svg class="chevron-icon h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="custom-dropdown">
                        <div class="custom-dropdown-item" data-value="1" onclick="selectGuest(1)">
                            <svg class="item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            <span>1 Guest</span>
                            <svg class="check-mark h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="custom-dropdown-item active" data-value="2" onclick="selectGuest(2)">
                            <svg class="item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                            <span>2 Guests</span>
                            <svg class="check-mark h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="custom-dropdown-item" data-value="3" onclick="selectGuest(3)">
                            <svg class="item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.97 5.97 0 00-.75-2.906A3.005 3.005 0 0119 17v1h-3zM4.75 14.094A5.97 5.97 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/></svg>
                            <span>3 Guests</span>
                            <svg class="check-mark h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                    <span class="hidden lg:block field-divider"></span>
                </div>

                <!-- Search Button -->
                <div class="lg:pl-2">
                    <button type="submit" id="searchBtn"
                            class="w-full lg:w-auto bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-bold py-3.5 px-8 rounded-xl lg:rounded-full text-sm transition-all duration-200 shadow-lg hover:shadow-brand-500/30 flex items-center justify-center gap-2 whitespace-nowrap disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg id="searchIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <svg id="searchSpinner" class="hidden h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="searchBtnText">Search</span>
                    </button>
                </div>
            </form>
        </div>
        <!-- Error banner -->
        <div id="errorBanner" class="hidden mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 text-center"></div>
    </div>

    <!-- Trust signals -->
    <div class="relative z-20 flex flex-wrap items-center justify-center gap-6 mt-8 animate-fade-in-up animate-delay-3">
        <div class="flex items-center gap-2 text-blue-200/60 text-xs font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Secure Booking
        </div>
        <div class="flex items-center gap-2 text-blue-200/60 text-xs font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
            Best Price Guarantee
        </div>
        <div class="flex items-center gap-2 text-blue-200/60 text-xs font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z" clip-rule="evenodd"/></svg>
            Instant Confirmation
        </div>
    </div>
</section>

<!-- ============================================================
     RESULTS SECTION
============================================================ -->
<section class="max-w-5xl mx-auto px-4 -mt-12 relative z-30 pb-16 min-h-[30vh]">

    <!-- Loading state -->
    <div id="loadingState" class="hidden flex flex-col items-center justify-center py-24 gap-4">
        <div class="spinner"></div>
        <p class="text-slate-500 font-medium">Searching for the best rates…</p>
    </div>

    <!-- Empty / initial state -->
    <div id="emptyState" class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <p class="text-slate-400 text-lg font-medium mb-1">Ready when you are</p>
        <p class="text-slate-300 text-sm">Select your dates above to see available rooms and prices.</p>
    </div>

    <!-- Results grid -->
    <div id="resultsContainer" class="hidden">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Available Rooms</h2>
                <p class="text-slate-400 text-sm mt-1">Select a room to complete your booking</p>
            </div>
            <span id="nightCount" class="text-sm font-semibold text-brand-600 bg-brand-50 px-4 py-2 rounded-full"></span>
        </div>
        <div id="resultsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-8"></div>
    </div>
</section>

<!-- Error Modal -->
<div id="errorModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <div class="bg-gradient-to-br from-red-500 to-red-600 px-8 py-6 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur rounded-full mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Booking Failed</h2>
            <p class="text-red-100 text-sm mt-1 font-medium">We couldn't complete your request</p>
        </div>
        <div class="px-8 py-6">
            <p id="errorModalMsg" class="text-slate-600 text-sm text-center leading-relaxed"></p>
        </div>
        <div class="px-8 pb-6">
            <button onclick="document.getElementById('errorModal').classList.add('hidden')"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition text-sm">
                Try Again
            </button>
        </div>
    </div>
</div>

<!-- Booking Success Modal -->
<div id="bookingModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 px-8 py-6 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur rounded-full mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Booking Confirmed!</h2>
            <p id="modalRef" class="text-emerald-100 text-sm mt-1 font-medium"></p>
        </div>
        <!-- Summary -->
        <div class="px-8 py-6">
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Room</span>
                    <span id="modalRoom" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="border-t border-dashed border-slate-100"></div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Check-in</span>
                    <span id="modalCheckIn" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Check-out</span>
                    <span id="modalCheckOut" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="border-t border-dashed border-slate-100"></div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Guests</span>
                    <span id="modalGuests" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Meal Plan</span>
                    <span id="modalMeal" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="border-t border-dashed border-slate-100"></div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400 font-medium">Duration</span>
                    <span id="modalNights" class="text-slate-800 font-semibold"></span>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-8 pb-6">
            <button onclick="document.getElementById('bookingModal').classList.add('hidden')"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl transition text-sm">
                Done
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form        = document.getElementById('searchForm');
    const errorBanner = document.getElementById('errorBanner');
    const loadingEl   = document.getElementById('loadingState');
    const emptyEl     = document.getElementById('emptyState');
    const resultsEl   = document.getElementById('resultsContainer');
    const gridEl      = document.getElementById('resultsGrid');
    const nightEl     = document.getElementById('nightCount');
    const searchBtn   = document.getElementById('searchBtn');

    // Track last search params for booking
    let lastSearch = { checkIn: '', checkOut: '', guests: '', meal: '' };

    // ---- Flatpickr date pickers -------------------------------------------
    var today = new Date();
    today.setHours(0,0,0,0);

    var fpCheckIn = flatpickr('#check_in_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'M j, Y',
        minDate: 'today',
        disableMobile: false,
        monthSelectorType: 'static',
        onChange: function(selectedDates) {
            if (selectedDates.length) {
                var nextDay = new Date(selectedDates[0]);
                nextDay.setDate(nextDay.getDate() + 1);
                fpCheckOut.set('minDate', nextDay);
                // Clear check-out if it's before the new min
                var coVal = fpCheckOut.selectedDates[0];
                if (coVal && coVal < nextDay) {
                    fpCheckOut.clear();
                }
                // Auto-open check-out picker
                setTimeout(function() { fpCheckOut.open(); }, 200);
            }
            clearError();
        }
    });

    var fpCheckOut = flatpickr('#check_out_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'M j, Y',
        minDate: new Date(today.getTime() + 86400000),
        disableMobile: false,
        monthSelectorType: 'static',
        onChange: function() { clearError(); }
    });

    // ---- Custom dropdown logic ---------------------------------------------
    window.toggleDropdown = function(type) {
        var wrap = document.getElementById('guestDropdownWrap');
        wrap.classList.toggle('dropdown-open');
    };

    window.selectGuest = function(val) {
        document.getElementById('guest_count').value = val;
        document.getElementById('guestLabel').textContent = val + (val === 1 ? ' Guest' : ' Guests');
        // Update active state
        var items = document.querySelectorAll('#guestDropdownWrap .custom-dropdown-item');
        items.forEach(function(el) { el.classList.toggle('active', el.dataset.value == val); });
        document.getElementById('guestDropdownWrap').classList.remove('dropdown-open');
    };

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#guestDropdownWrap')) document.getElementById('guestDropdownWrap').classList.remove('dropdown-open');
    });

    // ---- jQuery Validation setup -------------------------------------------
    var $searchForm = $('#searchForm');
    var validator = $searchForm.validate({
        ignore: [],
        rules: {
            check_in_date:  { required: true },
            check_out_date: { required: true }
        },
        messages: {
            check_in_date:  { required: 'Please select a check-in date.' },
            check_out_date: { required: 'Please select a check-out date.' }
        },
        errorPlacement: function(error, element) {
            // All errors shown in banner
        },
        showErrors: function(errorMap, errorList) {
            if (errorList.length) {
                // Show only the first error
                showError(errorList[0].message);
            }
            this.defaultShowErrors();
        },
        submitHandler: function(formEl, e) {
            e.preventDefault();
            clearError();
            doSearch();
        },
        highlight: function(element) {
            $(element).closest('.field-group').find('.field-icon').css('color', '#ef4444');
        },
        unhighlight: function(element) {
            $(element).closest('.field-group').find('.field-icon').css('color', '');
        }
    });

    // ---- Helpers ----------------------------------------------------------
    function nights(checkIn, checkOut) {
        const diff = (new Date(checkOut) - new Date(checkIn)) / 86400000;
        return Math.max(0, diff);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency', currency: 'INR', maximumFractionDigits: 0
        }).format(amount);
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function showLoading() {
        emptyEl.classList.add('hidden');
        resultsEl.classList.add('hidden');
        loadingEl.classList.remove('hidden');
    }

    function hideLoading() {
        loadingEl.classList.add('hidden');
    }

    function showError(msg) {
        document.getElementById('errorModalMsg').textContent = msg;
        document.getElementById('errorModal').classList.remove('hidden');
    }

    function clearError() {
        document.getElementById('errorModal').classList.add('hidden');
    }

    // ---- Render a single room card ----------------------------------------
    function buildCard(room, nightCount) {
        const card = document.createElement('div');
        card.className = 'result-card bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden';

        const imgHtml = room.image_url
            ? `<div class="relative h-56 overflow-hidden">
                 <img src="${ room.image_url }" alt="${ room.room_type } Room"
                      class="w-full h-full object-cover ${ room.available ? '' : 'grayscale opacity-60' }" loading="lazy"
                      onerror="this.parentElement.style.display='none'" />
                 ${ room.available
                   ? `<div class="absolute top-4 left-4">
                       <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur text-xs font-bold px-3 py-1.5 rounded-full text-emerald-700 shadow-sm">
                         <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Available
                       </span>
                     </div>`
                   : `<div class="absolute inset-0 bg-slate-900/40 flex items-center justify-center">
                       <span class="bg-red-500/90 backdrop-blur text-white text-sm font-bold px-5 py-2 rounded-full tracking-wide uppercase shadow-lg">Sold Out</span>
                     </div>` }
               </div>`
            : '';

        if (room.available) {
            const roPerNight = nightCount > 0 ? Math.round(room.price_room_only / nightCount) : 0;
            const bfPerNight = nightCount > 0 ? Math.round(room.price_breakfast / nightCount) : 0;
            const hasDiscount = room.discount_percent > 0;
            const discountBadge = hasDiscount
                ? `<span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-100">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                     ${room.discount_percent}% OFF
                   </span>`
                : '';
            const discountLabels = hasDiscount
                ? `<p class="text-green-600 text-[10px] font-semibold mt-0.5">${room.discount_labels.join(' + ')}</p>`
                : '';
            const roOriginal = hasDiscount
                ? `<span class="text-slate-400 text-sm line-through ml-2">${formatCurrency(room.original_room_only)}</span>`
                : '';
            const bfOriginal = hasDiscount
                ? `<span class="text-slate-400 text-sm line-through ml-2">${formatCurrency(room.original_breakfast)}</span>`
                : '';

            card.innerHTML = `
                ${ imgHtml }
                <div class="p-6">
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-block text-[11px] font-bold uppercase tracking-wider ${ room.room_type === 'Deluxe' ? 'text-amber-600' : 'text-brand-600' }">
                                ${ room.room_type === 'Deluxe' ? '★ Premium Collection' : '● Standard' }
                            </span>
                            ${ discountBadge }
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">${ room.room_type } Room</h3>
                        <p class="text-slate-400 text-xs mt-1">Up to 3 guests · Free cancellation</p>
                        ${ discountLabels }
                    </div>

                    <!-- Room Only Option -->
                    <div class="border-t border-slate-100 pt-4 pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full border bg-slate-50 text-slate-500 border-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z" clip-rule="evenodd"/></svg>
                                        Room Only
                                    </span>
                                </div>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-extrabold text-slate-900 tracking-tight">${ formatCurrency(room.price_room_only) }</p>
                                    ${ roOriginal }
                                </div>
                                <p class="text-slate-400 text-[11px] mt-0.5">${ nightCount } night${ nightCount !== 1 ? 's' : '' }${ nightCount > 1 ? ' · ' + formatCurrency(roPerNight) + '/night' : '' }</p>
                            </div>
                            <button onclick="bookRoom(${room.room_type_id}, '${room.room_type}', 'room_only')"
                                    class="bg-brand-600 hover:bg-brand-700 active:scale-[0.97] text-white font-bold py-2.5 px-5 rounded-xl text-xs transition-all duration-200 shadow-sm hover:shadow-lg flex items-center gap-1.5 whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Book
                            </button>
                        </div>
                    </div>

                    <!-- With Breakfast Option -->
                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full border bg-orange-50 text-orange-600 border-orange-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>
                                        With Breakfast
                                    </span>
                                </div>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-extrabold text-slate-900 tracking-tight">${ formatCurrency(room.price_breakfast) }</p>
                                    ${ bfOriginal }
                                </div>
                                <p class="text-slate-400 text-[11px] mt-0.5">${ nightCount } night${ nightCount !== 1 ? 's' : '' }${ nightCount > 1 ? ' · ' + formatCurrency(bfPerNight) + '/night' : '' }</p>
                            </div>
                            <button onclick="bookRoom(${room.room_type_id}, '${room.room_type}', 'breakfast_included')"
                                    class="bg-orange-500 hover:bg-orange-600 active:scale-[0.97] text-white font-bold py-2.5 px-5 rounded-xl text-xs transition-all duration-200 shadow-sm hover:shadow-lg flex items-center gap-1.5 whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Book
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            card.innerHTML = `
                ${ imgHtml }
                <div class="p-6 opacity-60">
                    <div class="mb-4">
                        <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            ${ room.room_type === 'Deluxe' ? '★ Premium Collection' : '● Standard' }
                        </span>
                        <h3 class="text-xl font-bold text-slate-600">${ room.room_type } Room</h3>
                    </div>
                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-slate-500 text-sm">No rooms available for your selected dates.</p>
                    </div>
                </div>
            `;
        }

        return card;
    }

    // ---- Render results ---------------------------------------------------
    function renderResults(rooms, checkIn, checkOut) {
        gridEl.innerHTML = '';
        const n = nights(checkIn, checkOut);
        nightEl.textContent = `${ n } night${ n !== 1 ? 's' : '' }`;
        rooms.forEach(room => gridEl.appendChild(buildCard(room, n)));
        hideLoading();
        resultsEl.classList.remove('hidden');
    }

    // ---- Book a room ------------------------------------------------------
    window.bookRoom = async function(roomTypeId, roomTypeName, mealPlan) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        try {
            const res = await fetch('/api/book', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    room_type_id: roomTypeId,
                    check_in_date: lastSearch.checkIn,
                    check_out_date: lastSearch.checkOut,
                    guest_count: parseInt(lastSearch.guests),
                    meal_plan: mealPlan,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Booking failed');
                showError(msg);
                return;
            }
            const n = nights(lastSearch.checkIn, lastSearch.checkOut);
            const mealText = mealPlan === 'breakfast_included' ? 'With Breakfast' : 'Room Only';
            document.getElementById('modalRef').textContent = `Reference #${data.booking_id}`;
            document.getElementById('modalRoom').textContent = `${roomTypeName} Room`;
            document.getElementById('modalCheckIn').textContent = formatDate(lastSearch.checkIn);
            document.getElementById('modalCheckOut').textContent = formatDate(lastSearch.checkOut);
            document.getElementById('modalGuests').textContent = `${lastSearch.guests} Guest${lastSearch.guests > 1 ? 's' : ''}`;
            document.getElementById('modalMeal').textContent = mealText;
            document.getElementById('modalNights').textContent = `${n} Night${n !== 1 ? 's' : ''}`;
            document.getElementById('bookingModal').classList.remove('hidden');
        } catch {
            showError('Network error while booking. Please try again.');
        }
    };

    // ---- Search (called by jQuery Validation submitHandler) ----------------
    async function doSearch() {
        const checkIn  = document.getElementById('check_in_date').value;
        const checkOut = document.getElementById('check_out_date').value;
        const guests   = document.getElementById('guest_count').value;

        showLoading();
        searchBtn.disabled = true;
        document.getElementById('searchIcon').classList.add('hidden');
        document.getElementById('searchSpinner').classList.remove('hidden');
        document.getElementById('searchBtnText').textContent = 'Searching…';
        lastSearch = { checkIn, checkOut, guests };

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const body = new URLSearchParams({
                check_in_date:  checkIn,
                check_out_date: checkOut,
                guest_count:    guests,
                _token:         csrfToken,
            });

            const response = await fetch('/api/search', {
                method:  'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString(),
            });

            const data = await response.json();

            if (!response.ok) {
                // Laravel validation error (422) or server error
                if (response.status === 422 && data.errors) {
                    const msgs = Object.values(data.errors).flat().join(' ');
                    hideLoading();
                    showError(msgs);
                    emptyEl.classList.remove('hidden');
                } else {
                    hideLoading();
                    showError('An unexpected error occurred. Please try again.');
                    emptyEl.classList.remove('hidden');
                }
                return;
            }

            renderResults(data, checkIn, checkOut);

        } catch (err) {
            hideLoading();
            showError('Network error — please check your connection and try again.');
            emptyEl.classList.remove('hidden');
        } finally {
            searchBtn.disabled = false;
            document.getElementById('searchSpinner').classList.add('hidden');
            document.getElementById('searchIcon').classList.remove('hidden');
            document.getElementById('searchBtnText').textContent = 'Search';
        }
    }

})();
</script>
@endpush
