<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full">

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-gradient-to-r from-blue-950 to-blue-900 rounded-t-2xl px-6 py-5 flex items-center gap-4 shadow-lg">
        <div class="w-11 h-11 rounded-xl bg-yellow-400 flex items-center justify-center flex-shrink-0 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <div>
            <h2 class="text-white font-black text-lg leading-tight">Login Admin</h2>
            <p class="text-blue-300 text-xs mt-0.5">Masuk ke panel pengelolaan laporan</p>
        </div>
    </div>

    {{-- ===== FORM BODY ===== --}}
    <div class="bg-white rounded-b-2xl shadow-xl px-6 py-6 border border-t-0 border-gray-100">

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login">
            <div class="grid grid-cols-1 gap-5">

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input
                            wire:model="form.email"
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="admin@example.com"
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none"
                        >
                    </div>
                    @error('form.email')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <input
                            wire:model="form.password"
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none"
                        >
                    </div>
                    @error('form.password')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me + Forgot Password --}}
                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            wire:model="form.remember"
                            id="remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            name="remember"
                        >
                        <span class="text-xs text-gray-500 font-medium">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                            Lupa password?
                        </a>
                    @endif
                </div>

                {{-- Tombol Submit --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-950 to-blue-800 hover:from-blue-900 hover:to-blue-700 disabled:opacity-60 text-white py-3 px-6 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/30"
                >
                    <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk ke Dashboard
                    </span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                </button>

                {{-- Back to Home --}}
                <div class="text-center">
                    <a href="/" class="text-xs text-gray-400 hover:text-gray-600 transition">
                        ← Kembali ke halaman pelaporan
                    </a>
                </div>

            </div>
        </form>
    </div>

</div>
