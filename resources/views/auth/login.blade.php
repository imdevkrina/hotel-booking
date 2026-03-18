@extends('layouts.app')

@section('title', 'Admin Login — Hotel Booking')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-brand-50 rounded-2xl mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Admin Login</h1>
                <p class="text-slate-500 text-sm mt-1">Sign in to manage inventory &amp; discounts</p>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25"
                           placeholder="admin@hotel.com" />
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Password</label>
                    <input type="password" id="password" name="password"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25"
                           placeholder="••••••••" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember"
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500/25 h-4 w-4" />
                    <label for="remember" class="text-sm text-slate-600">Remember me</label>
                </div>
                <button type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-bold py-3.5 rounded-xl text-base transition-all duration-150 shadow-md">
                    Sign In
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs text-slate-400 text-center">
                    Demo credentials: <span class="font-semibold text-slate-500">admin@hotel.com</span> / <span class="font-semibold text-slate-500">admin@123</span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#loginForm').validate({
        rules: {
            email: { required: true, email: true },
            password: { required: true }
        },
        messages: {
            email: { required: 'Please enter your email address.', email: 'Please enter a valid email address.' },
            password: { required: 'Please enter your password.' }
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('border-red-400').removeClass('border-slate-200');
        },
        unhighlight: function(element) {
            $(element).removeClass('border-red-400').addClass('border-slate-200');
        }
    });
});
</script>
@endpush
