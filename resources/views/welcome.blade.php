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
    <body class="bg-gradient-to-br from-slate-100 via-blue-100 to-slate-200 dark:from-slate-900 dark:via-blue-950 dark:to-slate-900 min-h-screen text-slate-900 dark:text-white flex flex-col">
        <header class="w-full max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-slate-800 dark:text-white/90">Bell Sekolah Otomatis</h1>
            </div>
            <nav class="flex items-center gap-3">
                <button id="dark-toggle" class="w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 hover:bg-white dark:hover:bg-white/20 flex items-center justify-center transition" aria-label="Toggle dark mode">
                    <svg id="sun-icon" class="w-4 h-4 text-yellow-500 hidden" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="moon-icon" class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm rounded-lg bg-white/80 dark:bg-white/10 hover:bg-white dark:hover:bg-white/20 text-slate-700 dark:text-white transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition">Login Admin</a>
                    @endauth
                @endif
            </nav>
        </header>

        <main class="flex-1 w-full max-w-6xl mx-auto px-2 sm:px-4 py-4 sm:py-6 flex flex-col lg:flex-row gap-4 sm:gap-6 items-start overflow-x-hidden">
            {{-- Digital Clock --}}
            <div class="w-full lg:w-[380px] xl:w-[400px] shrink-0">
                <div class="bg-white/80 dark:bg-white/5 backdrop-blur-xl rounded-2xl border border-slate-200 dark:border-white/10 p-4 sm:p-6 lg:p-8 text-center">
                    <p class="text-sm text-blue-500 dark:text-blue-300/80 font-medium uppercase tracking-widest mb-2">{{ $todayDate }}</p>
                    <p class="text-sm text-slate-500 dark:text-white/50 mb-6">{{ $dayName }}</p>
                    <div class="clock-display">
                        <div class="text-4xl sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl 2xl:text-7xl font-light tracking-[0.1em] tabular-nums text-slate-900 dark:text-white overflow-hidden" id="clock">00:00:00</div>
                        <div class="text-base sm:text-lg text-slate-400 dark:text-white/40 mt-2" id="clock-date"></div>
                    </div>
                    @php $hasBell = $schedules->contains(fn($s) => $s->audio_file && $s->is_active); @endphp
                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-center gap-2 text-sm {{ $isSchoolDay ? 'text-green-400' : 'text-yellow-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $isSchoolDay ? 'bg-green-400 animate-pulse' : 'bg-yellow-400' }}"></span>
                            <span>{{ $isSchoolDay ? 'Hari Sekolah Aktif' : 'Hari Libur / Tidak Ada Jadwal' }}</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-sm {{ $hasBell ? 'text-emerald-400' : 'text-red-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $hasBell ? 'bg-emerald-400 animate-pulse' : 'bg-red-400' }}"></span>
                            <span>{{ $hasBell ? 'Engine Bell Aktif' : 'Engine Bell Tidak Aktif' }}</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-sm {{ $schoolStatus === 'Berlangsung' ? 'text-blue-400' : ($schoolStatus === 'Selesai' ? 'text-orange-400' : 'text-slate-400 dark:text-white/40') }}">
                            <span class="w-2 h-2 rounded-full {{ $schoolStatus === 'Berlangsung' ? 'bg-blue-400 animate-pulse' : ($schoolStatus === 'Selesai' ? 'bg-orange-400' : 'bg-slate-300 dark:bg-white/20') }}"></span>
                            <span id="school-status-text">
                                @if ($schoolStatus === 'Libur')
                                    {{ $isSchoolDay ? 'Tidak ada jadwal' : 'Hari libur' }}
                                @elseif ($schoolStatus === 'Belum masuk')
                                    Jam sekolah dimulai {{ $firstBell }}
                                @elseif ($schoolStatus === 'Selesai')
                                    Jam sekolah selesai {{ $lastBell ? 'pukul ' . $lastBell : '' }}
                                @else
                                    Jam sekolah berlangsung
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bell Schedule --}}
            <div class="flex-1 w-full">
                <div class="bg-white/80 dark:bg-white/5 backdrop-blur-xl rounded-2xl border border-slate-200 dark:border-white/10 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-white/5 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Jadwal Bell Hari Ini</h2>
                        <span class="text-xs text-slate-400 dark:text-white/40">{{ $schedules->count() }} event</span>
                    </div>

                    @if ($schedules->isNotEmpty())
                        @php
                            $now = now()->format('H:i');
                            $nextFound = false;
                        @endphp
                        <div class="divide-y divide-slate-200 dark:divide-white/5">
                            @foreach ($schedules as $schedule)
                                @php
                                    $time = $schedule->time?->format('H:i');
                                    $isNow = $time && $time === $now;
                                    $isPast = $time < $now;
                                    $isNext = !$isPast && !$isNow && !$nextFound;
                                    if ($isNext) $nextFound = true;
                                @endphp
                                <div id="schedule-{{ $schedule->id }}" class="px-6 py-4 flex items-center gap-4 transition {{ $isNow ? 'bg-blue-100 dark:bg-blue-500/10 border-l-2 border-blue-400' : ($isPast ? 'opacity-40' : 'hover:bg-slate-100 dark:hover:bg-white/5') }} {{ $isNext ? 'ring-1 ring-emerald-400/30 bg-emerald-50 dark:bg-emerald-500/5' : '' }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium {{ $isNow ? 'text-blue-600 dark:text-blue-300' : ($isPast ? 'text-slate-400 dark:text-white/50' : 'text-slate-700 dark:text-white/80') }}">
                                            {{ $schedule->name }}
                                            <span class="ml-2 inline-flex items-center gap-1 text-xs"
                                                  data-label="{{ $schedule->id }}">
                                                @if ($isNow)
                                                    <span class="text-blue-500 dark:text-blue-400">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400 animate-pulse inline-block"></span> Berlangsung
                                                    </span>
                                                @elseif ($isPast)
                                                    <span class="text-slate-300 dark:text-white/30">Sudah bunyi</span>
                                                @elseif ($isNext)
                                                    <span class="text-emerald-500 dark:text-emerald-400">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 inline-block"></span> Selanjutnya
                                                    </span>
                                                @endif
                                            </span>
                                        </p>
                                        <p class="text-xs {{ $isNow ? 'text-blue-500/70 dark:text-blue-400/70' : 'text-slate-400 dark:text-white/40' }} mt-0.5">
                                            {{ $schedule->time?->format('H:i') }}
                                        </p>
                                    </div>
                                    @if ($schedule->audio_file)
                                        <div class="shrink-0">
                                            <button onclick="playBell('{{ $schedule->audio_file }}')" class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isNow ? 'bg-blue-500/20 text-blue-500 dark:text-blue-400 hover:bg-blue-500/30' : 'bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-white/40 hover:bg-slate-200 dark:hover:bg-white/10 hover:text-slate-500 dark:hover:text-white/60' }} transition">
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
                            <p class="text-slate-500 dark:text-white/60">Tidak ada jadwal bell untuk hari ini.</p>
                            <p class="text-slate-400 dark:text-white/30 text-sm mt-1">Selamat beristirahat!</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>

        <footer class="w-full max-w-6xl mx-auto px-4 py-4 text-center text-xs text-slate-400 dark:text-white/20">
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
            // Dark mode
            (function() {
                const stored = localStorage.getItem('darkMode');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = stored !== null ? stored === 'true' : prefersDark;
                document.documentElement.classList.toggle('dark', isDark);

                document.getElementById('dark-toggle')?.addEventListener('click', function() {
                    const nowDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('darkMode', nowDark);
                    document.getElementById('sun-icon')?.classList.toggle('hidden', !nowDark);
                    document.getElementById('moon-icon')?.classList.toggle('hidden', nowDark);
                });

                const sun = document.getElementById('sun-icon');
                const moon = document.getElementById('moon-icon');
                if (sun && moon) {
                    sun.classList.toggle('hidden', !isDark);
                    moon.classList.toggle('hidden', isDark);
                }
            })();

            const schedules = @json($bellData);
            const firstBell = @json($firstBell);
            const lastBell = @json($lastBell);
            const isSchoolDay = @json($isSchoolDay);

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

            function updateSchoolStatus() {
                const now = new Date();
                const current = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                const el = document.getElementById('school-status-text');
                if (!el) return;

                if (!isSchoolDay || schedules.length === 0) {
                    el.textContent = !isSchoolDay ? 'Hari libur' : 'Tidak ada jadwal';
                } else if (firstBell && current < firstBell) {
                    el.textContent = 'Jam sekolah dimulai ' + firstBell;
                } else if (lastBell && current > lastBell) {
                    el.textContent = 'Jam sekolah selesai pukul ' + lastBell;
                } else {
                    el.textContent = 'Jam sekolah berlangsung';
                }
            }

            function updateScheduleLabels() {
                const now = new Date();
                const current = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                let nextFound = false;

                for (const s of schedules) {
                    const row = document.getElementById('schedule-' + s.id);
                    const label = row?.querySelector('[data-label="' + s.id + '"]');
                    if (!row || !label) continue;

                    const isPast = s.time < current;
                    const isNow = s.time === current;
                    const isNext = !isPast && !isNow && !nextFound;
                    if (isNext) nextFound = true;

                    row.className = row.className
                        .replace(/(?:^|\s)(bg-blue-500\/10|border-l-2|border-blue-400|opacity-40|ring-1|ring-emerald-400\/30|bg-emerald-500\/5|hover:bg-white\/5)/g, '')
                        + (isNow ? ' bg-blue-500/10 border-l-2 border-blue-400' : isPast ? ' opacity-40' : isNext ? ' ring-1 ring-emerald-400/30 bg-emerald-500/5' : ' hover:bg-white/5');

                    if (isNow) {
                        label.innerHTML = '<span class="text-blue-400"><span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse inline-block"></span> Berlangsung</span>';
                    } else if (isPast) {
                        label.innerHTML = '<span class="text-white/30">Sudah bunyi</span>';
                    } else if (isNext) {
                        label.innerHTML = '<span class="text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> Selanjutnya</span>';
                    } else {
                        label.innerHTML = '';
                    }
                }
            }

            setInterval(function() {
                checkSchedules();
                updateSchoolStatus();
                updateScheduleLabels();
            }, 10000);
            checkSchedules();
            updateSchoolStatus();
            updateScheduleLabels();

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
