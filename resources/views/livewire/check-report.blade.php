<?php

use Livewire\Volt\Component;
use App\Models\Report;

new class extends Component {
    public $search = '';
    public $results = null;

    public function findReport()
    {
        if (strlen($this->search) < 4) {
            $this->results = null;
            return;
        }

        $this->results = Report::where('nomor_hp', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}; ?>

<div class="w-full">

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-gradient-to-r from-blue-950 to-blue-900 rounded-t-2xl px-6 py-5 flex items-center gap-4 shadow-lg">
        <div class="w-11 h-11 rounded-xl bg-yellow-400 flex items-center justify-center flex-shrink-0 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <div>
            <h2 class="text-white font-black text-lg leading-tight">Cek Status Laporan</h2>
            <p class="text-blue-300 text-xs mt-0.5">Masukkan nomor HP yang digunakan saat melapor</p>
        </div>
    </div>

    {{-- ===== BODY ===== --}}
    <div class="bg-white rounded-b-2xl shadow-xl px-6 py-6 border border-t-0 border-gray-100">

        {{-- Input Pencarian --}}
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                Nomor WhatsApp
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live="search"
                    wire:keyup="findReport"
                    placeholder="Contoh: 08123456..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none"
                >
            </div>
            @if(strlen($search) > 0 && strlen($search) < 4)
                <p class="text-blue-400 text-[11px] mt-1.5 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                    Ketik minimal 4 karakter untuk mencari
                </p>
            @endif
        </div>

        {{-- ===== NOTE WHATSAPP ===== --}}
        <div class="mt-4 flex items-start gap-2.5 p-3.5 bg-green-50 border border-green-200 rounded-xl">
            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-500 flex items-center justify-center mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <p class="text-[11px] text-green-700 leading-relaxed">
                <span class="font-black">Notifikasi WhatsApp:</span>
                Apabila laporan Anda sudah selesai ditangani, Anda akan mendapatkan pesan WhatsApp otomatis dari petugas.
                <span class="font-bold">Pastikan nomor HP yang Anda masukkan sudah benar.</span>
            </p>
        </div>

        {{-- ===== HASIL PENCARIAN ===== --}}
        <div class="mt-5 space-y-3">

            @if($results && $results->count() > 0)

                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
                    {{ $results->count() }} laporan ditemukan
                </p>

                @foreach($results as $report)
                    @php
                        $statusConfig = match($report->status) {
                            'selesai' => [
                                'badge'  => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                'dot'    => 'bg-emerald-500',
                                'label'  => 'SELESAI',
                            ],
                            'proses'  => [
                                'badge'  => 'bg-amber-100 text-amber-700 border border-amber-200',
                                'dot'    => 'bg-amber-500',
                                'label'  => 'DIPROSES',
                            ],
                            default   => [
                                'badge'  => 'bg-gray-100 text-gray-600 border border-gray-200',
                                'dot'    => 'bg-gray-400',
                                'label'  => 'MENUNGGU',
                            ],
                        };

                        $urgensiConfig = match($report->urgensi) {
                            'Tinggi' => 'bg-red-50 text-red-600 border border-red-100',
                            'Sedang' => 'bg-amber-50 text-amber-600 border border-amber-100',
                            'Rendah' => 'bg-blue-50 text-blue-600 border border-blue-100',
                            default  => 'bg-gray-50 text-gray-500 border border-gray-100',
                        };
                    @endphp

                    <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-blue-50/30 hover:border-blue-100 transition duration-150">
                        <div class="flex justify-between items-start gap-3">

                            {{-- Info Laporan --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                                    {{ $report->created_at->format('d M Y, H:i') }}
                                </p>
                                <p class="text-sm font-bold text-gray-800 leading-snug truncate">
                                    {{ $report->jenis_gangguan }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">
                                    {{ $report->dampak_wilayah }}
                                </p>

                                {{-- Urgensi Badge --}}
                                @if($report->urgensi)
                                    <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $urgensiConfig }}">
                                        Urgensi: {{ $report->urgensi }}
                                    </span>
                                @endif
                            </div>

                            {{-- Status Badge --}}
                            <div class="flex-shrink-0 text-right">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase {{ $statusConfig['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>

                        </div>
                    </div>
                @endforeach

            @elseif(strlen($search) >= 4)
                <div class="py-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Data tidak ditemukan</p>
                    <p class="text-xs text-gray-400 mt-1">Pastikan nomor HP yang dimasukkan sudah benar.</p>
                </div>
            @endif

        </div>
    </div>

</div>