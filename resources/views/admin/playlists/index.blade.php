<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-900 dark:text-white">Playlist Pembuka & Penutup</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Admin Navigation --}}
            <div class="mb-6 flex items-center gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="px-4 py-2 rounded-lg font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Dashboard
                </a>
                <a href="{{ route('admin.audio.index') }}"
                   class="px-4 py-2 rounded-lg font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Manajemen Audio
                </a>
                <a href="{{ route('admin.schedules') }}"
                   class="px-4 py-2 rounded-lg font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Jadwal Bell
                </a>
                <a href="{{ route('admin.school-days') }}"
                   class="px-4 py-2 rounded-lg font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Hari Sekolah
                </a>
                <a href="{{ route('admin.playlists.index') }}"
                   class="px-4 py-2 rounded-lg font-medium bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white">
                    Playlist
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Playlist</h3>
                    <a href="{{ route('admin.playlists.create') }}"
                       class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                        + Tambah Playlist
                    </a>
                </div>

                @if ($playlists->isEmpty())
                    <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        Belum ada playlist. <a href="{{ route('admin.playlists.create') }}" class="text-blue-500 hover:underline">Buat baru</a>.
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($playlists as $p)
                            <div class="px-6 py-4 flex items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $p->type === 'opening' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' }}">
                                            {{ $p->type === 'opening' ? 'Pembuka' : 'Penutup' }}
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $p->name }}</span>
                                        @if (!$p->is_active)
                                            <span class="text-xs text-gray-400">(nonaktif)</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ count($p->audio_assets) }} aset audio
                                        @if ($p->time_range_start)
                                            · {{ $p->time_range_start->format('H:i') }}
                                            @if ($p->time_range_end) – {{ $p->time_range_end->format('H:i') }} @endif
                                        @endif
                                        @if ($p->action_after)
                                            · Aksi: {{ $p->action_after }}
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('admin.playlists.edit', $p) }}"
                                       class="px-3 py-1.5 rounded-lg text-sm bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 hover:bg-amber-200 dark:hover:bg-amber-900/50 transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.playlists.destroy', $p) }}"
                                          onsubmit="return confirm('Hapus playlist ini?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
