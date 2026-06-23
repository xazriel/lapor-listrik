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

        // Data laporan per minggu (12 minggu terakhir)
        $weeklyReports = Report::selectRaw(
                'YEARWEEK(created_at, 1) as year_week,
                 MIN(DATE(created_at)) as week_start,
                 COUNT(*) as total,
                 SUM(CASE WHEN status = \'selesai\' THEN 1 ELSE 0 END) as selesai,
                 SUM(CASE WHEN status = \'proses\' THEN 1 ELSE 0 END) as proses,
                 SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN urgensi = \'Tinggi\' THEN 1 ELSE 0 END) as tinggi,
                 SUM(CASE WHEN urgensi = \'Sedang\' THEN 1 ELSE 0 END) as sedang,
                 SUM(CASE WHEN urgensi = \'Rendah\' THEN 1 ELSE 0 END) as rendah'
            )
            ->where('created_at', '>=', now()->subWeeks(12)->startOfWeek())
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get()
            ->map(function ($row, $index) {
                $start = \Carbon\Carbon::parse($row->week_start);
                $end   = $start->copy()->addDays(6);
                $row->label = 'Minggu ' . ($index + 1) . ' (' . $start->format('d M') . ' – ' . $end->format('d M') . ')';
                $row->short_label = $start->format('d M');
                return $row;
            });

        $totalLaporan  = Report::count();
        $totalPending  = Report::where('status', 'pending')->count();
        $totalProses   = Report::where('status', 'proses')->count();
        $totalSelesai  = Report::where('status', 'selesai')->count();
        $totalTinggi   = Report::where('urgensi', 'Tinggi')->count();

        return [
            'reports'        => $reports,
            'byCategory'     => $byCategory,
            'weeklyReports'  => $weeklyReports,
            'totalLaporan'   => $totalLaporan,
            'totalPending'   => $totalPending,
            'totalProses'    => $totalProses,
            'totalSelesai'   => $totalSelesai,
            'totalTinggi'    => $totalTinggi,
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

    {{-- ===== SECTION: LAPORAN PER MINGGU ===== --}}
    <div class="px-6 pb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-gray-700 uppercase tracking-wide">Laporan Per Minggu</h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">Rekap 12 minggu terakhir berdasarkan status & urgensi</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-teal-600 bg-teal-50 border border-teal-200 px-3 py-1 rounded-full uppercase">{{ $weeklyReports->count() }} Minggu</span>
            </div>

            @if($weeklyReports->isEmpty())
                <div class="flex items-center justify-center h-40 text-gray-400 text-sm italic">
                    Belum ada data laporan mingguan.
                </div>
            @else
                {{-- Chart Mingguan --}}
                <div class="relative mb-5" style="height: 240px;">
                    <canvas id="weeklyChart"></canvas>
                </div>

                {{-- Tabel Mingguan --}}
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-700 text-white">
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider rounded-tl-lg">Periode</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">Total</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">Pending</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">Proses</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">Selesai</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">🔴 Tinggi</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center">🟡 Sedang</th>
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-center rounded-tr-lg">🔵 Rendah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($weeklyReports as $week)
                                <tr class="hover:bg-teal-50/40 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-700 text-[11px]">{{ $week->label }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-black text-gray-800">{{ $week->total }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->pending > 0)
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-black rounded-full">{{ $week->pending }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->proses > 0)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 text-[10px] font-black rounded-full">{{ $week->proses }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->selesai > 0)
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-black rounded-full">{{ $week->selesai }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->tinggi > 0)
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 border border-red-200 text-[10px] font-black rounded-full">{{ $week->tinggi }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->sedang > 0)
                                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 border border-yellow-200 text-[10px] font-black rounded-full">{{ $week->sedang }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($week->rendah > 0)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-600 border border-blue-200 text-[10px] font-black rounded-full">{{ $week->rendah }}</span>
                                        @else
                                            <span class="text-gray-300 text-[10px]">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td class="px-4 py-2.5 text-[10px] font-black text-gray-600 uppercase">Total Keseluruhan</td>
                                <td class="px-4 py-2.5 text-center text-sm font-black text-gray-800">{{ $weeklyReports->sum('total') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-amber-700">{{ $weeklyReports->sum('pending') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-blue-700">{{ $weeklyReports->sum('proses') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-emerald-700">{{ $weeklyReports->sum('selesai') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-red-700">{{ $weeklyReports->sum('tinggi') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-yellow-700">{{ $weeklyReports->sum('sedang') }}</td>
                                <td class="px-4 py-2.5 text-center text-[11px] font-black text-blue-700">{{ $weeklyReports->sum('rendah') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== SECTION: MODEL C4.5 ===== --}}
    @php $m = config('c45_model'); @endphp
    <div class="px-6 pb-4 space-y-4">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-black text-gray-700 uppercase tracking-wide">Informasi Model C4.5</h2>
                <p class="text-[11px] text-gray-400">Dilatih dengan Python scikit-learn — criterion=entropy</p>
            </div>
        </div>

        {{-- Stat Cards Akurasi --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-violet-600 to-violet-800 rounded-xl p-4 text-white shadow-lg">
                <div class="text-2xl font-black">{{ number_format($m['akurasi_training'], 1) }}%</div>
                <div class="text-[10px] font-bold text-violet-200 uppercase tracking-wider mt-1">Akurasi Training</div>
            </div>
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl p-4 text-white shadow-lg">
                <div class="text-2xl font-black">{{ number_format($m['akurasi_testing'], 1) }}%</div>
                <div class="text-[10px] font-bold text-blue-200 uppercase tracking-wider mt-1">Akurasi Testing</div>
            </div>
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl p-4 text-white shadow-lg">
                <div class="text-2xl font-black">{{ number_format($m['akurasi_cv'], 1) }}%</div>
                <div class="text-[10px] font-bold text-emerald-200 uppercase tracking-wider mt-1">CV 5-Fold</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="text-2xl font-black text-gray-800">{{ $m['total_data'] }}</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1">Data Training</div>
            </div>
        </div>

        {{-- Confusion Matrix + Rules --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Confusion Matrix --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-xs font-black text-gray-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Confusion Matrix
                    <span class="text-[10px] text-gray-400 font-normal normal-case">({{ $m['total_testing'] }} data testing)</span>
                </h3>
                @php
                    $klsArr  = $m['kelas'];
                    $cm      = $m['confusion_matrix'];
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr>
                                <th class="p-2 text-[10px] text-gray-400 font-bold text-left">Aktual ↓ / Prediksi →</th>
                                @foreach($klsArr as $col)
                                    <th class="p-2 bg-slate-700 text-white rounded text-center text-[10px] font-black">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($klsArr as $rowKls)
                                <tr>
                                    <td class="p-2 font-black text-gray-600 text-[11px]">{{ $rowKls }}</td>
                                    @foreach($klsArr as $colKls)
                                        @php
                                            $val = $cm[$rowKls][$colKls] ?? 0;
                                            $cls = $rowKls === $colKls
                                                ? ($val > 0 ? 'bg-emerald-50 text-emerald-700 font-black border border-emerald-200' : 'bg-gray-50 text-gray-300')
                                                : ($val > 0 ? 'bg-red-50 text-red-600 font-bold border border-red-100'   : 'bg-gray-50 text-gray-300');
                                        @endphp
                                        <td class="p-2 text-center rounded {{ $cls }} text-sm">{{ $val }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-[10px] text-gray-400 italic">
                    ✅ Hijau = prediksi benar &nbsp;|&nbsp; 🔴 Merah = kesalahan klasifikasi
                </p>
            </div>

            {{-- Decision Tree Rules --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-xs font-black text-gray-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                    Rules Pohon Keputusan C4.5
                </h3>
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @foreach($m['rules'] as $i => $rule)
                        @php
                            $badgeColor = match($rule['hasil']) {
                                'Tinggi' => 'bg-red-100 text-red-700 border-red-200',
                                'Sedang' => 'bg-amber-100 text-amber-700 border-amber-200',
                                default  => 'bg-blue-100 text-blue-700 border-blue-200',
                            };
                        @endphp
                        <div class="flex items-start gap-2 p-2.5 rounded-lg bg-gray-50 border border-gray-100 hover:border-violet-200 transition">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-violet-100 text-violet-700 text-[10px] font-black flex items-center justify-center">{{ $i+1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-600 leading-snug font-mono">{{ $rule['kondisi'] }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-1.5 py-0.5 text-[9px] font-black border rounded {{ $badgeColor }} uppercase">{{ $rule['hasil'] }}</span>
                                    <span class="text-[9px] text-gray-400 italic">{{ $rule['alasan'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer info bar --}}
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl p-4 flex flex-wrap items-center gap-4">
            <div class="text-xs text-slate-300 font-medium flex-1">
                <span class="text-white font-black">Algoritma C4.5</span> &nbsp;|&nbsp;
                criterion = entropy &nbsp;|&nbsp;
                {{ $m['total_atribut'] }} Atribut Input &nbsp;|&nbsp;
                {{ count($m['kelas']) }} Kelas Output &nbsp;|&nbsp;
                CV ±{{ number_format($m['cv_std'], 2) }}%
            </div>
            <div class="flex gap-2">
                @foreach($m['kelas'] as $kls)
                    @php $dot = match($kls) { 'Tinggi' => 'bg-red-400', 'Sedang' => 'bg-amber-400', default => 'bg-blue-400' }; @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-slate-600 rounded-full text-[10px] text-white font-bold">
                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>{{ $kls }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== DATASET TRAINING C4.5 (Collapsible) ===== --}}
    <div class="px-6 pb-4" x-data="{ open: false }">
        {{-- Toggle Button --}}
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-3.5 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-violet-300 hover:shadow-md transition group">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-sm font-black text-gray-700">Dataset Training C4.5</p>
                    <p class="text-[11px] text-gray-400">python/dataset.csv — {{ config('c45_model.total_data') }} baris data</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full uppercase">CSV</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        {{-- Dataset Table (collapsible) --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mt-3 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

            @php
                $csvPath = base_path('python/dataset.csv');
                $csvRows = [];
                $csvHeader = [];
                if (file_exists($csvPath)) {
                    $file = fopen($csvPath, 'r');
                    $csvHeader = fgetcsv($file);
                    while (($row = fgetcsv($file)) !== false) {
                        $csvRows[] = $row;
                    }
                    fclose($file);
                }
                $urgensiCount = ['Tinggi' => 0, 'Sedang' => 0, 'Rendah' => 0];
                foreach ($csvRows as $row) {
                    $label = $row[3] ?? '';
                    if (isset($urgensiCount[$label])) $urgensiCount[$label]++;
                }
            @endphp

            {{-- Mini Stats Bar --}}
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center gap-4">
                <span class="text-[11px] font-bold text-gray-500">{{ count($csvRows) }} baris</span>
                <span class="text-gray-300">|</span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-600">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span> Tinggi: {{ $urgensiCount['Tinggi'] }}
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Sedang: {{ $urgensiCount['Sedang'] }}
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span> Rendah: {{ $urgensiCount['Rendah'] }}
                </span>
                <span class="ml-auto text-[10px] text-gray-400 italic">Sumber: python/dataset.csv</span>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-slate-700 text-white z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider">#</th>
                            @foreach($csvHeader as $head)
                                <th class="px-4 py-2.5 text-[10px] font-black uppercase tracking-wider">{{ $head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($csvRows as $i => $row)
                            @php
                                $urgensi = $row[3] ?? '';
                                $rowBg = match($urgensi) {
                                    'Tinggi' => 'hover:bg-red-50/50',
                                    'Sedang' => 'hover:bg-amber-50/50',
                                    default  => 'hover:bg-blue-50/50',
                                };
                                $badge = match($urgensi) {
                                    'Tinggi' => 'bg-red-100 text-red-700 border-red-200',
                                    'Sedang' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    default  => 'bg-blue-100 text-blue-700 border-blue-200',
                                };
                            @endphp
                            <tr class="transition {{ $rowBg }}">
                                <td class="px-4 py-2 text-gray-400 font-mono">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 text-gray-700 font-semibold">{{ $row[0] ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $row[1] ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $row[2] ?? '-' }} jam</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 text-[10px] font-black border rounded-full {{ $badge }} uppercase">
                                        {{ $row[3] ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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

    // ===== Chart: Laporan per Kategori =====
    var labels   = @json($byCategory->pluck('jenis_gangguan'));
    var data     = @json($byCategory->pluck('total'));
    var colors   = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
    var bgColors = labels.map(function(_, i){ return colors[i % colors.length]; });

    var ctx = document.getElementById('categoryChart');
    if (ctx) {
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
    }

    // ===== Chart: Laporan per Minggu =====
    var weeklyLabels  = @json($weeklyReports->pluck('short_label'));
    var weeklyTotal   = @json($weeklyReports->pluck('total'));
    var weeklySelesai = @json($weeklyReports->pluck('selesai'));
    var weeklyProses  = @json($weeklyReports->pluck('proses'));
    var weeklyPending = @json($weeklyReports->pluck('pending'));

    var wCtx = document.getElementById('weeklyChart');
    if (wCtx) {
        new Chart(wCtx, {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [
                    {
                        label: 'Selesai',
                        data: weeklySelesai,
                        backgroundColor: '#10b98199',
                        borderColor: '#10b981',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        stack: 'status',
                    },
                    {
                        label: 'Proses',
                        data: weeklyProses,
                        backgroundColor: '#3b82f699',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        stack: 'status',
                    },
                    {
                        label: 'Pending',
                        data: weeklyPending,
                        backgroundColor: '#f59e0b99',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        stack: 'status',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 11, weight: '700' },
                            color: '#6b7280',
                            boxWidth: 12,
                            padding: 16,
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        callbacks: {
                            afterBody: function(items) {
                                var total = items.reduce(function(sum, i){ return sum + i.parsed.y; }, 0);
                                return ['', 'Total: ' + total + ' laporan'];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, weight: '700' },
                            color: '#6b7280',
                        }
                    },
                    y: {
                        stacked: true,
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
    }
});
</script>
@endpush