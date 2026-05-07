<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Panel Monitoring Gangguan Listrik
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Klasifikasi Urgensi Otomatis menggunakan Algoritma C4.5</p>
            </div>
            <a href="/"
               class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Lihat Form Pelaporan
            </a>
        </div>
    </x-slot>

    <livewire:admindashboard />

</x-app-layout>