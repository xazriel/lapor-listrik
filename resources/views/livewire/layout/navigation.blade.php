<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-gradient-to-r from-blue-950 to-blue-900 border-b border-blue-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- ===== LOGO / BRAND ===== --}}
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    {{-- Ikon Petir PLN --}}
                    <div class="w-9 h-9 rounded-lg bg-yellow-400 flex items-center justify-center shadow-md flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-white font-black text-base tracking-wide leading-none">
                            PLN <span class="text-yellow-400">ADMIN</span>
                        </div>
                        <div class="text-blue-300 text-[10px] font-medium tracking-wider leading-none mt-0.5">
                            MONITORING SISTEM
                        </div>
                    </div>
                </div>

                {{-- Nav Links Desktop --}}
                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}"
                       wire:navigate
                       class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-semibold transition
                              {{ request()->routeIs('dashboard')
                                  ? 'bg-blue-800 text-white'
                                  : 'text-blue-200 hover:text-white hover:bg-blue-800/60' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    <a href="/"
                       wire:navigate
                       class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-semibold text-blue-200 hover:text-white hover:bg-blue-800/60 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Form Laporan
                    </a>
                </div>
            </div>

            {{-- ===== USER DROPDOWN Desktop ===== --}}
            <div class="hidden sm:flex items-center gap-3">
                {{-- Badge Admin --}}
                <span class="px-2.5 py-1 bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-[10px] font-black rounded-full uppercase tracking-widest">
                    Administrator
                </span>

                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-blue-800/60 hover:bg-blue-800 border border-blue-700 text-sm font-medium text-white transition">
                            {{-- Avatar --}}
                            <div class="w-7 h-7 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                                  x-text="name"
                                  x-on:profile-updated.window="name = $event.detail.name"
                                  class="max-w-[120px] truncate">
                            </span>
                            <svg class="h-4 w-4 text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Info User --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-xs font-black text-gray-800 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('Profil Saya') }}
                            </div>
                        </x-dropdown-link>

                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    <div class="flex items-center gap-2 text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('Keluar') }}
                                    </div>
                                </x-dropdown-link>
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- ===== HAMBURGER Mobile ===== --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-blue-300 hover:text-white hover:bg-blue-800 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== RESPONSIVE MENU Mobile ===== --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-blue-800">
        <div class="px-4 pt-3 pb-2 space-y-1">
            <a href="{{ route('dashboard') }}"
               wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-semibold
                      {{ request()->routeIs('dashboard')
                          ? 'bg-blue-800 text-white'
                          : 'text-blue-200 hover:text-white hover:bg-blue-800/60' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="/"
               wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-semibold text-blue-200 hover:text-white hover:bg-blue-800/60 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Form Laporan
            </a>
        </div>

        {{-- User Info Mobile --}}
        <div class="pt-3 pb-4 border-t border-blue-800 px-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-white"
                         x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                         x-text="name"
                         x-on:profile-updated.window="name = $event.detail.name">
                    </div>
                    <div class="text-xs text-blue-300">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('profile') }}"
                   wire:navigate
                   class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-blue-200 hover:text-white hover:bg-blue-800/60 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil Saya
                </a>
                <button wire:click="logout" class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm text-red-400 hover:text-white hover:bg-red-700/60 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </button>
            </div>
        </div>
    </div>
</nav>