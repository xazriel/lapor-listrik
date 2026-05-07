<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Lapor Listrik') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen antialiased" style="background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

        {{-- Brand Logo --}}
        <a href="/" class="flex items-center gap-2.5 mb-8 group">
            <div class="w-10 h-10 rounded-xl bg-yellow-400 flex items-center justify-center shadow-md group-hover:scale-105 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-950" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <span class="text-white font-black text-base leading-none">Lapor Listrik</span>
                <span class="block text-blue-300 text-[11px] leading-none mt-0.5">Desa Tanjung Durian</span>
            </div>
        </a>

        {{-- Card Konten --}}
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-blue-300/40 text-[11px]">
            &copy; {{ date('Y') }} Sistem Informasi Gangguan Listrik
        </p>

    </div>

</body>
</html>
