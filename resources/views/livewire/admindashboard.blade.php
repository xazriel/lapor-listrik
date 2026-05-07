<?php

use Livewire\Volt\Component;
use App\Models\Report;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public function with(): array
    {
        $reports = Report::orderByRaw("FIELD(urgensi, 'Tinggi', 'Sedang', 'Rendah')")
                    ->orderBy('created_at', 'desc')
                    ->get();

        $byCategory = Report::selectRaw('jenis_gangguan, COUNT(*) as total')
                        ->groupBy('jenis_gangguan')
                        ->orderByDesc('total')
                        ->get();

        $totalLaporan  = Report::count();
        $totalPending  = Report::where('status', 'pending')->count();
        $totalProses   = Report::where('status', 'proses')->count();
        $totalSelesai  = Report::where('status', 'selesai')->count();
        $totalTinggi   = Report::where('urgensi', 'Tinggi')->count();

        return [
            'reports'       => $reports,
            'byCategory'    => $byCategory,
            'totalLaporan'  => $totalLaporan,
            'totalPending'  => $totalPending,
            'totalProses'   => $totalProses,
            'totalSelesai'  => $totalSelesai,
            'totalTinggi'   => $totalTinggi,
        ];
    }

    public function updateStatus($id, $status): void
    {
        $report = Report::find($id);
        if ($report) {
            $report->update(['status' => $status]);

            if ($status === 'selesai') {
                $this->sendWhatsApp($report);
            }

            session()->flash('message', 'Status laporan berhasil diperbarui menjadi ' . strtoupper($status));
        }
    }

    protected function sendWhatsApp($report): void
    {
        $token = "cUpADgNKy8LQkFg1LdgT";

        $pesan = "Halo *" . $report->nama_pelapor . "*,\n\n";
        $pesan .= "Laporan Anda mengenai *" . $report->jenis_gangguan . "* di wilayah *" . $report->dampak_wilayah . "* telah dideteksi sebagai *SELESAI*.\n\n";
        $pesan .= "Terima kasih telah melapor. Petugas kami telah menangani gangguan tersebut.\n\n";
        $pesan .= "_Pesan otomatis dari Sistem Informasi YAI_";

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => $token])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target'      => $report->nomor_hp,
                    'message'     => $pesan,
                    'countryCode' => '62',
                ]);

            logger("Respon Fonnte: " . $response->body());
        } catch (\Exception $e) {
            logger("Gagal mengirim WA: " . $e->getMessage());
        }
    }
}; ?>

{{-- TIDAK ADA <script> di sini, semua dipindah ke @push --}}

<div>
    {{-- Pesan Berhasil (Alert) --}}
    @if (session()->has('message'))
        <div class="m-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm shadow-sm rounded-r-lg flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('message') }}</span>
                @if(str_contains(session('message'), 'SELESAI'))
                    <span class="ml-2 px-2 py-0.5 bg-emerald-200 text-emerald-800 text-[10px] rounded-full font-bold uppercase tracking-tighter">WA Terkirim</span>
                @endif
            </div>
            <button onclick="this.parentElement.style.display='none'" class="text-emerald-500 hover:text-emerald-700 font-bold">×</button>
        </div>
    @endif

    {{-- ===== STAT CARDS ===== --}}
    <div class="px-6 pt-4 pb-2 grid grid-cols-2 sm:grid-cols-4 gap-4">

        {{-- Total Laporan --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-gray-800">{{ $totalLaporan }}</div>
                <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">Total Laporan</div>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-amber-600">{{ $totalPending }}</div>
                <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">Menunggu</div>
            </div>
        </div>

        {{-- Diproses --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-blue-600">{{ $totalProses }}</div>
                <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">Diproses</div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ $totalSelesai }}</div>
                <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wide">Selesai</div>
            </div>
        </div>
    </div>

    {{-- ===== CHART SECTION ===== --}}
    <div class="px-6 pb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-black text-gray-700 uppercase tracking-wide">Laporan per Kategori Gangguan</h2>
                    <p class="text-[11px] text-gray-400 mt-0.5">Jumlah pelaporan masuk berdasarkan jenis gangguan</p>
                </div>
                @if($totalTinggi > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-600 text-[10px] font-black rounded-full border border-red-100 uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                        {{ $totalTinggi }} Urgensi Tinggi
                    </span>
                @endif
            </div>

            @if($byCategory->isEmpty())
                <div class="flex items-center justify-center h-40 text-gray-400 text-sm italic">
                    Belum ada data untuk ditampilkan.
                </div>
            @else
                <div class="relative" style="height: 260px;">
                    <canvas id="categoryChart"></canvas>
                </div>

                {{-- Legend Tabel Mini --}}
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($byCategory as $index => $cat)
                        @php
                            $colors = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
                            $c = $colors[$index % count($colors)];
                        @endphp
                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-gray-50">
                            <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:{{ $c }}"></span>
                            <span class="text-[11px] text-gray-600 truncate flex-1">{{ $cat->jenis_gangguan }}</span>
                            <span class="text-[11px] font-black text-gray-800">{{ $cat->total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ===== TABEL UTAMA ===== --}}
    <div class="overflow-x-auto px-6 pb-6">
        <table class="w-full text-left border-collapse bg-white rounded-xl shadow-sm overflow-hidden">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Data Pelapor</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Detail Kejadian</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Wilayah</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Tingkat Urgensi</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status Kini</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-center">Tindakan Petugas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($reports as $report)
                    <tr class="hover:bg-gray-50/50 transition duration-150">
                        {{-- Data Pelapor --}}
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-800">{{ $report->nama_pelapor }}</div>
                            <div class="text-xs text-gray-500 font-medium">{{ $report->nomor_hp }}</div>
                        </td>

                        {{-- Detail Kejadian --}}
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 leading-snug">{{ $report->jenis_gangguan }}</div>
                            <div class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $report->durasi_padam }} Jam Padam</div>
                        </td>

                        {{-- Wilayah --}}
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-600 leading-relaxed max-w-[150px] italic">
                                {{ $report->dampak_wilayah }}
                            </div>
                        </td>

                        {{-- Urgensi --}}
                        <td class="px-6 py-4">
                            @php
                                $color = match($report->urgensi) {
                                    'Tinggi' => 'bg-red-50 text-red-600 border-red-100',
                                    'Sedang' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'Rendah' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    default  => 'bg-gray-50 text-gray-500 border-gray-100'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $color }} uppercase">
                                {{ $report->urgensi }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold {{ $report->status == 'selesai' ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ strtoupper($report->status) }}
                            </span>
                        </td>

                        {{-- Tombol Aksi --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center items-center gap-2">
                                @if($report->status !== 'proses' && $report->status !== 'selesai')
                                    <button wire:click="updateStatus({{ $report->id }}, 'proses')"
                                        class="px-3 py-1.5 bg-white border border-amber-200 text-amber-600 text-[10px] font-black rounded-lg hover:bg-amber-50 transition shadow-sm">
                                        PROSES
                                    </button>
                                @endif

                                @if($report->status !== 'selesai')
                                    <button wire:click="updateStatus({{ $report->id }}, 'selesai')"
                                        class="px-3 py-1.5 bg-emerald-600 text-white text-[10px] font-black rounded-lg hover:bg-emerald-700 transition shadow-md">
                                        SELESAI
                                    </button>
                                @endif

                                @if($report->status === 'selesai')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        TERTANGANI
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                            Belum ada laporan gangguan yang masuk saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== CHART.JS + INISIALISASI (semua dalam satu @push) ===== --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var labels   = @json($byCategory->pluck('jenis_gangguan'));
    var data     = @json($byCategory->pluck('total'));
    var colors   = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
    var bgColors = labels.map(function(_, i){ return colors[i % colors.length]; });

    var ctx = document.getElementById('categoryChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: data,
                backgroundColor: bgColors.map(function(c){ return c + 'cc'; }),
                borderColor: bgColors,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.parsed.y + ' laporan';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11, weight: '700' },
                        color: '#6b7280',
                        maxRotation: 30,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                        color: '#9ca3af',
                    }
                }
            }
        }
    });
});
</script>
@endpush