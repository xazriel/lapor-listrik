<?php

use Livewire\Volt\Component;
use App\Models\Report;
use App\Services\ClassificationService;
use Illuminate\Support\Facades\Session;

new class extends Component {
    public $nama_pelapor = '';
    public $nomor_hp = '';
    public $alamat_lengkap = '';
    public $jenis_gangguan = '';
    public $dampak_wilayah = '';
    public $durasi_padam = '';

    public function save(ClassificationService $service)
    {
        $this->validate([
            'nama_pelapor' => 'required|min:3',
            'nomor_hp'     => 'required|numeric',
            'alamat_lengkap' => 'required',
            'jenis_gangguan' => 'required',
            'dampak_wilayah' => 'required',
            'durasi_padam'   => 'required|numeric',
        ]);

        $urgensi = $service->classifyUrgensi(
            $this->jenis_gangguan,
            $this->dampak_wilayah,
            $this->durasi_padam
        );

        Report::create([
            'nama_pelapor'   => $this->nama_pelapor,
            'nomor_hp'       => $this->nomor_hp,
            'alamat_lengkap' => $this->alamat_lengkap,
            'jenis_gangguan' => $this->jenis_gangguan,
            'dampak_wilayah' => $this->dampak_wilayah,
            'durasi_padam'   => $this->durasi_padam,
            'urgensi'        => $urgensi,
            'status'         => 'pending',
        ]);

        Session::flash('message', $urgensi);
        $this->reset();
    }
}; ?>

<div class="w-full">

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-gradient-to-r from-blue-950 to-blue-900 rounded-t-2xl px-6 py-5 flex items-center gap-4 shadow-lg">
        <div class="w-11 h-11 rounded-xl bg-yellow-400 flex items-center justify-center flex-shrink-0 shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
            </svg>
        </div>
        <div>
            <h2 class="text-white font-black text-lg leading-tight">Form Laporan Gangguan Listrik</h2>
            <p class="text-blue-300 text-xs mt-0.5">Desa Tanjung Durian — Klasifikasi otomatis C4.5</p>
        </div>
    </div>

    {{-- ===== NOTIFIKASI SUKSES ===== --}}
    @if (session()->has('message'))
        @php
            $urgensi = session('message');
            $urgensiConfig = match($urgensi) {
                'Tinggi' => ['bg' => 'bg-red-50', 'border' => 'border-red-400', 'text' => 'text-red-700', 'badge' => 'bg-red-500', 'icon_color' => 'text-red-500'],
                'Sedang' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-400', 'text' => 'text-amber-700', 'badge' => 'bg-amber-500', 'icon_color' => 'text-amber-500'],
                default  => ['bg' => 'bg-blue-50', 'border' => 'border-blue-400', 'text' => 'text-blue-700', 'badge' => 'bg-blue-500', 'icon_color' => 'text-blue-500'],
            };
        @endphp
        <div class="{{ $urgensiConfig['bg'] }} border-l-4 {{ $urgensiConfig['border'] }} px-5 py-4 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 flex-shrink-0 {{ $urgensiConfig['icon_color'] }}" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="font-bold text-sm {{ $urgensiConfig['text'] }}">Laporan berhasil dikirim!</p>
                <p class="text-xs {{ $urgensiConfig['text'] }} mt-0.5 opacity-80">
                    Sistem mengklasifikasikan tingkat urgensi:
                    <span class="inline-block mt-1 px-2.5 py-0.5 {{ $urgensiConfig['badge'] }} text-white text-[10px] font-black rounded-full uppercase tracking-wider">
                        {{ $urgensi }}
                    </span>
                </p>
            </div>
        </div>
    @endif

    {{-- ===== FORM BODY ===== --}}
    <div class="bg-white rounded-b-2xl shadow-xl px-6 py-6 border border-t-0 border-gray-100">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 gap-5">

                {{-- Nama Pelapor --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Nama Pelapor
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.blur="nama_pelapor"
                               placeholder="Masukkan nama lengkap"
                               class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none">
                    </div>
                    @error('nama_pelapor')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Nomor HP + Durasi --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <input type="text" wire:model.blur="nomor_hp"
                                   placeholder="0812xxxx"
                                   class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none">
                        </div>
                        @error('nomor_hp')
                            <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                            Durasi Padam (Jam)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <input type="number" wire:model.blur="durasi_padam"
                                   placeholder="Contoh: 3"
                                   min="0"
                                   class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none">
                        </div>
                        @error('durasi_padam')
                            <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Jenis Gangguan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Jenis Gangguan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <select wire:model.change="jenis_gangguan"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none bg-white">
                            <option value="">-- Pilih Jenis Gangguan --</option>
                            <option value="Kabel Putus">Kabel Putus</option>
                            <option value="Trafo Meledak">Trafo Meledak</option>
                            <option value="Tiang Listrik Roboh">Tiang Listrik Roboh</option>
                            <option value="Padam Total">Padam Total</option>
                            <option value="Lampu Jalan Mati">Lampu Jalan Mati</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    @error('jenis_gangguan')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Dampak Wilayah --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Dampak Wilayah
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <select wire:model.change="dampak_wilayah"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none appearance-none bg-white">
                            <option value="">-- Pilih Dampak Wilayah --</option>
                            <option value="Satu Rumah">Hanya Satu Rumah</option>
                            <option value="Satu RT">Satu RT</option>
                            <option value="Fasilitas Umum">Fasilitas Umum / Kantor Desa</option>
                            <option value="Seluruh Desa">Seluruh Desa</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    @error('dampak_wilayah')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Alamat Lengkap --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Alamat Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <textarea wire:model.blur="alamat_lengkap"
                                  rows="3"
                                  placeholder="RT/RW, nama jalan, patokan lokasi..."
                                  class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none resize-none"></textarea>
                    </div>
                    @error('alamat_lengkap')
                        <p class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Tombol Submit --}}
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-950 to-blue-800 hover:from-blue-900 hover:to-blue-700 disabled:opacity-60 text-white py-3 px-6 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/30 mt-1">

                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                        Kirim Laporan
                    </span>

                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses Klasifikasi C4.5...
                    </span>
                </button>

            </div>
        </form>
    </div>
</div>