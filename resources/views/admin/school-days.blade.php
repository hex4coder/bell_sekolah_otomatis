<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight text-gray-900 dark:text-white">Manajemen Hari Sekolah</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Admin Navigation --}}
            <div class="mb-6 flex items-center gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.audio.index') }}"
                   class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.audio.*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Manajemen Audio
                </a>
                <a href="{{ route('admin.schedules') }}"
                   class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.schedules*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Jadwal Bell
                </a>
                <a href="{{ route('admin.school-days') }}"
                   class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.school-days') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Hari Sekolah
                </a>
            </div>

            <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Atur hari apa saja yang merupakan hari sekolah aktif. Klik tombol Edit untuk mengubah status hari.</p>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($days as $day)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $day->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    @if ($loop->index === 0) Minggu
                                    @elseif ($loop->index === 6) Sabtu
                                    @else Hari kerja
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $day->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                    {{ $day->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <button onclick="openEdit({{ $day->id }}, {{ $day->day_of_week }}, '{{ $day->name }}', {{ $day->is_active ? 'true' : 'false' }})"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Edit Hari</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="days[0][id]" id="editDayId">
                <input type="hidden" name="days[0][day_of_week]" id="editDayOfWeek">
                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Hari</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="editDayName"></p>
                </div>
                <div class="mb-6">
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Status Hari Sekolah</p>
                    <div onclick="toggleStatus()" id="editStatusPanel" class="w-full p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 flex items-center justify-between bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-700">
                        <div class="flex items-center gap-3">
                            <div id="editStatusIcon" class="w-5 h-5 rounded-full bg-red-500"></div>
                            <div>
                                <p class="font-semibold text-red-700 dark:text-red-300" id="editDayStatusLabel">Nonaktif</p>
                                <p class="text-xs text-red-500/70 dark:text-red-400/70" id="editDayStatusDesc">Hari ini tidak masuk sekolah</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                    <input type="checkbox" name="days[0][is_active]" value="1" id="editDayActive" class="hidden">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const msg = document.getElementById('flash-success')?.dataset.message;
            if (msg) Swal.fire({ icon: 'success', title: 'Berhasil', text: msg, timer: 3000, showConfirmButton: false });
        })();

        let editActive = false;

        function updateStatusUI() {
            const label = document.getElementById('editDayStatusLabel');
            const desc = document.getElementById('editDayStatusDesc');
            const panel = document.getElementById('editStatusPanel');
            const icon = document.getElementById('editStatusIcon');

            if (editActive) {
                label.textContent = 'Aktif';
                label.className = 'font-semibold text-green-700 dark:text-green-300';
                desc.textContent = 'Hari sekolah aktif — jadwal bell ditampilkan';
                panel.className = 'w-full p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 flex items-center justify-between bg-green-50 border-green-300 dark:bg-green-900/20 dark:border-green-700';
                icon.className = 'w-5 h-5 rounded-full bg-green-500';
            } else {
                label.textContent = 'Nonaktif';
                label.className = 'font-semibold text-red-700 dark:text-red-300';
                desc.textContent = 'Hari ini tidak masuk sekolah';
                panel.className = 'w-full p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 flex items-center justify-between bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-700';
                icon.className = 'w-5 h-5 rounded-full bg-red-500';
            }
        }

        function toggleStatus() {
            editActive = !editActive;
            document.getElementById('editDayActive').checked = editActive;
            updateStatusUI();
        }

        function openEdit(id, dayOfWeek, name, isActive) {
            editActive = isActive;
            document.getElementById('editDayId').value = id;
            document.getElementById('editDayOfWeek').value = dayOfWeek;
            document.getElementById('editDayName').textContent = name;
            document.getElementById('editDayActive').checked = isActive;
            document.getElementById('editForm').action = '{{ route('admin.school-days.update') }}';
            updateStatusUI();
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
