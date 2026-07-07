<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Bell Sekolah Otomatis</title>
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 min-h-screen text-white flex flex-col">
        <header class="w-full max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-white/90">Bell Sekolah Otomatis</h1>
            </div>
            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm rounded-lg bg-white/10 hover:bg-white/20 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm rounded-lg bg-blue-500 hover:bg-blue-600 transition">Login Admin</a>
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6 flex flex-col lg:flex-row gap-6 items-start">
            {{-- Digital Clock --}}
            <div class="w-full lg:w-[380px] shrink-0">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 p-8 text-center">
                    <p class="text-sm text-blue-300/80 font-medium uppercase tracking-widest mb-2">{{ $todayDate }}</p>
                    <p class="text-sm text-white/50 mb-6">{{ $dayName }}</p>
                    <div class="clock-display">
                        <div class="text-7xl font-light tracking-[0.1em] tabular-nums text-white" id="clock">00:00:00</div>
                        <div class="text-lg text-white/40 mt-2" id="clock-date"></div>
                    </div>
                    @php $hasBell = $schedules->contains(fn($s) => $s->audio_file && $s->is_active); @endphp
                    <div class="mt-6 pt-6 border-t border-white/5 space-y-2">
                        <div class="flex items-center justify-center gap-2 text-sm {{ $isSchoolDay ? 'text-green-400' : 'text-yellow-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $isSchoolDay ? 'bg-green-400 animate-pulse' : 'bg-yellow-400' }}"></span>
                            <span>{{ $isSchoolDay ? 'Hari Sekolah Aktif' : 'Hari Libur / Tidak Ada Jadwal' }}</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-sm {{ $hasBell ? 'text-emerald-400' : 'text-red-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $hasBell ? 'bg-emerald-400 animate-pulse' : 'bg-red-400' }}"></span>
                            <span>{{ $hasBell ? 'Bell Aktif' : 'Bell Tidak Aktif' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bell Schedule --}}
            <div class="flex-1 w-full">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Jadwal Bell Hari Ini</h2>
                        <span class="text-xs text-white/40">{{ $schedules->count() }} event</span>
                    </div>

                    @if ($schedules->isNotEmpty())
                        <div class="divide-y divide-white/5">
                            @php $now = now()->format('H:i'); @endphp
                            @foreach ($schedules as $schedule)
                                @php
                                    $time = $schedule->time?->format('H:i');
                                    $isNow = $time && $time <= $now && $now < date('H:i', strtotime('+2 minutes', strtotime($time)));
                                    $isPast = $time < $now && !$isNow;
                                @endphp
                                <div class="px-6 py-4 flex items-center gap-4 transition {{ $isNow ? 'bg-blue-500/10 border-l-2 border-blue-400' : ($isPast ? 'opacity-40' : 'hover:bg-white/5') }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium {{ $isNow ? 'text-blue-300' : ($isPast ? 'text-white/50' : 'text-white/80') }}">
                                            {{ $schedule->name }}
                                            @if ($isNow)
                                                <span class="ml-2 inline-flex items-center gap-1 text-xs text-blue-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span> Berlangsung
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-xs {{ $isNow ? 'text-blue-400/70' : 'text-white/40' }} mt-0.5">
                                            {{ $schedule->time?->format('H:i') }}
                                        </p>
                                    </div>
                                    @if ($schedule->audio_file)
                                        <div class="shrink-0">
                                            <button onclick="playBell('{{ $schedule->audio_file }}')" class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isNow ? 'bg-blue-500/20 text-blue-400 hover:bg-blue-500/30' : 'bg-white/5 text-white/40 hover:bg-white/10 hover:text-white/60' }} transition">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-yellow-500/10 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-white/60">Tidak ada jadwal bell untuk hari ini.</p>
                            <p class="text-white/30 text-sm mt-1">Selamat beristirahat!</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>

        <footer class="w-full max-w-6xl mx-auto px-4 py-4 text-center text-xs text-white/20">
            &copy; {{ date('Y') }} Sistem Bell Sekolah Otomatis
        </footer>

        @php
            $bellData = $schedules
                ->filter(fn($s) => $s->audio_file && $s->time)
                ->map(fn($s) => [
                    'id' => $s->id,
                    'time' => $s->time->format('H:i'),
                    'audio_file' => $s->audio_file,
                ])
                ->values();
        @endphp

        <script>
            const schedules = @json($bellData);

            let playedIds = new Set();

            function updateClock() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('clock').textContent = hours + ':' + minutes + ':' + seconds;
            }
            updateClock();
            setInterval(updateClock, 1000);

            function playBell(filename) {
                const audio = new Audio('/audio/' + filename);
                audio.volume = 1;
                audio.play().catch(() => {});
            }

            function checkSchedules() {
                const now = new Date();
                const current = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

                for (const s of schedules) {
                    if (playedIds.has(s.id)) continue;
                    if (s.time === current) {
                        playedIds.add(s.id);
                        playBell(s.audio_file);
                    }
                }
            }

            setInterval(checkSchedules, 10000);
            checkSchedules();

            // Poll emergency bell every 5 seconds
            setInterval(async function() {
                try {
                    const res = await fetch('/api/emergency-bell');
                    const data = await res.json();
                    if (data.audio_file) {
                        playBell(data.audio_file);
                    }
                } catch (e) {}
            }, 5000);
        </script>
    </body>
</html>
