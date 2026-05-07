<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Listrik - Desa Tanjung Durian</title>
    <meta name="description" content="Sistem pelaporan gangguan listrik online untuk warga Desa Tanjung Durian. Laporkan gangguan dan cek status laporan Anda.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);">

    {{-- ===== NAVBAR ===== --}}
    <nav class="w-full px-4 py-3">
        <div class="max-w-2xl mx-auto flex items-center justify-between">

            {{-- Logo / Brand --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-yellow-400 flex items-center justify-center shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <span class="text-white font-black text-sm leading-none">Lapor Listrik</span>
                    <span class="block text-blue-300 text-[10px] leading-none mt-0.5">Desa Tanjung Durian</span>
                </div>
            </div>

            {{-- Tombol Admin --}}
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 bg-yellow-400 hover:bg-yellow-300 text-blue-950 text-xs font-black px-4 py-2 rounded-full shadow-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Admin Panel
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white border border-white/20 text-xs font-bold px-4 py-2 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Login Admin
                </a>
            @endauth
        </div>
    </nav>

    {{-- ===== HERO SECTION ===== --}}
    <div class="pt-6 pb-8 px-4 text-center">
        <div class="max-w-2xl mx-auto">

            {{-- Headline --}}
            <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight mb-3">
                Laporkan Gangguan Listrik
                <span class="block text-yellow-400">Desa Tanjung Durian</span>
            </h1>

            <p class="text-blue-200 text-sm leading-relaxed max-w-md mx-auto mb-8">
            </p>

        </div>
    </div>

    {{-- ===== KONTEN UTAMA ===== --}}
    <div class="px-4 pb-16">
        <div class="max-w-2xl mx-auto space-y-4">

            {{-- Form Pelaporan --}}
            <livewire:reportform />

            {{-- Cek Status --}}
            <livewire:check-report />

        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    <div class="pb-8 text-center px-4">
        <p class="text-blue-300/50 text-[11px]">
            &copy; {{ date('Y') }} Sistem Informasi Gangguan Listrik &mdash; Final Project
        </p>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>