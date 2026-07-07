<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl leading-tight">Manajemen Jadwal Bell</h2>
            <div class="flex items-center gap-2">
                <button onclick="openCopy()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition">
                    Copy Jadwal
                </button>
                <form action="{{ route('admin.schedules.reset') }}" method="POST" class="inline reset-form">
                    @csrf @method('DELETE')
                    <button type="button" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition btn-reset">
                        Reset Semua
                    </button>
                </form>
                <button onclick="openCreate()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    + Tambah Jadwal
                </button>
            </div>
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

            <div class="space-y-6">
                @foreach ($days as $day)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $day->name }}</h3>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $day->schedules->count() }} jadwal</span>
                                <form action="{{ route('admin.schedules.destroyDay', $day->day_of_week) }}" method="POST" class="inline destroy-day-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium btn-destroy-day" data-day="{{ $day->name }}">
                                        Hapus Semua
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if ($day->schedules->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700/30 border-b border-gray-200 dark:border-gray-700">
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Nama</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Waktu</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">File Audio</th>
                                            <th class="px-4 py-2 text-center font-medium text-gray-500 dark:text-gray-400">Status</th>
                                            <th class="px-4 py-2 text-center font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($day->schedules as $schedule)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $schedule->name }}</td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $schedule->time?->format('H:i') }}</td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 text-xs max-w-[200px] truncate">{{ $schedule->audio_file ? ($audioNameMap[$schedule->audio_file] ?? $schedule->audio_file) : '-' }}</td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $schedule->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                                        {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        @if ($schedule->audio_file)
                                                            <button onclick="playAudio('{{ asset('audio/' . $schedule->audio_file) }}')" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 transition" title="Putar audio">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                            </button>
                                                        @endif
                                                        <button onclick="openEdit({{ $schedule->id }})" class="p-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 transition" title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        </button>
                                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="button" class="p-1.5 rounded-lg bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 transition btn-delete" title="Hapus" data-name="{{ $schedule->name }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                Belum ada jadwal untuk hari {{ $day->name }}.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Copy Modal --}}
    <div id="copyModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Copy Jadwal ke Hari Lain</h3>
                <button onclick="document.getElementById('copyModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.schedules.copy') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sumber Hari</label>
                    <select name="source_day" id="copy_source_day" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach ([['Minggu',0],['Senin',1],['Selasa',2],['Rabu',3],['Kamis',4],['Jumat',5],['Sabtu',6]] as [$n,$v])
                            <option value="{{ $v }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tujuan Hari (satu atau lebih)</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach ([['Minggu',0],['Senin',1],['Selasa',2],['Rabu',3],['Kamis',4],['Jumat',5],['Sabtu',6]] as [$n,$v])
                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <input type="checkbox" name="target_days[]" value="{{ $v }}" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $n }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-700 dark:text-amber-300">
                    Semua jadwal yang sudah ada di hari tujuan akan dihapus dan diganti dengan jadwal dari hari sumber.
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">Copy Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Form Modal --}}
    <div id="formModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" id="modalTitle">Tambah Jadwal</h3>
                <button onclick="closeForm()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="scheduleForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="mb-4">
                    <label class="flex items-center gap-3 cursor-pointer mb-3">
                        <input type="checkbox" name="all_days" value="1" id="field_all_days" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Semua hari aktif</span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hari</label>
                    <select name="day_of_week" id="field_day_of_week" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach ([['Minggu',0],['Senin',1],['Selasa',2],['Rabu',3],['Kamis',4],['Jumat',5],['Sabtu',6]] as [$n,$v])
                            <option value="{{ $v }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Jadwal</label>
                    <input type="text" name="name" id="field_name" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Jam Ke-1">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu</label>
                    <input type="time" name="time" id="field_time" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File Audio</label>
                                                    <select name="audio_file" id="field_audio_file" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                                        <option value="">— Tidak ada audio —</option>
                                                        @foreach ($audioFiles as $af)
                                                            <option value="{{ $af->filename }}">{{ $af->name }}</option>
                                                        @endforeach
                                                    </select>
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" id="field_is_active" checked class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeForm()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Batal</button>
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

        document.getElementById('copy_source_day')?.addEventListener('change', updateCopyTargets);
        document.getElementById('field_all_days')?.addEventListener('change', function () {
            document.getElementById('field_day_of_week').disabled = this.checked;
        });

        document.addEventListener('click', function (e) {
            const resetBtn = e.target.closest('.btn-reset');
            if (resetBtn) {
                const form = resetBtn.closest('.reset-form');
                Swal.fire({
                    title: 'Reset semua jadwal?',
                    text: 'Semua jadwal bell akan dihapus. Tindakan ini tidak bisa dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, reset semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
                return;
            }

            const destroyDayBtn = e.target.closest('.btn-destroy-day');
            if (destroyDayBtn) {
                const form = destroyDayBtn.closest('.destroy-day-form');
                const day = destroyDayBtn.dataset.day;
                Swal.fire({
                    title: 'Hapus semua jadwal?',
                    text: 'Yakin ingin menghapus semua jadwal hari ' + day + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, hapus semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
                return;
            }

            const btn = e.target.closest('.btn-delete');
            if (!btn) return;
            const form = btn.closest('.delete-form');
            const name = btn.dataset.name;
            Swal.fire({
                title: 'Hapus jadwal?',
                text: 'Yakin ingin menghapus jadwal ' + name + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });

        const schedules = @json(collect($days)->flatMap(fn($d) => $d->schedules));

        function openCreate() {
            document.getElementById('modalTitle').textContent = 'Tambah Jadwal';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('scheduleForm').action = '{{ route('admin.schedules.store') }}';
            document.getElementById('field_all_days').checked = false;
            document.getElementById('field_day_of_week').disabled = false;
            document.getElementById('field_day_of_week').value = 1;
            document.getElementById('field_name').value = '';
            document.getElementById('field_time').value = '';
            document.getElementById('field_audio_file').value = '';
            document.getElementById('field_is_active').checked = true;
            document.getElementById('formModal').classList.remove('hidden');
        }

        function openEdit(id) {
            const s = schedules.find(s => s.id === id);
            if (!s) return;
            document.getElementById('modalTitle').textContent = 'Edit Jadwal';
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('scheduleForm').action = '{{ url('admin/schedules') }}/' + id;
            document.getElementById('field_day_of_week').value = s.day_of_week;
            document.getElementById('field_name').value = s.name;
            document.getElementById('field_time').value = s.time?.substring(0, 5) || '';
            document.getElementById('field_audio_file').value = s.audio_file || '';
            document.getElementById('field_is_active').checked = s.is_active;
            document.getElementById('formModal').classList.remove('hidden');
        }

        function openCopy() {
            document.getElementById('copyModal').classList.remove('hidden');
            // Uncheck target checkboxes that match source
            updateCopyTargets();
        }

        function updateCopyTargets() {
            const source = parseInt(document.getElementById('copy_source_day').value);
            document.querySelectorAll('input[name="target_days[]"]').forEach(cb => {
                if (parseInt(cb.value) === source) {
                    cb.checked = false;
                    cb.disabled = true;
                    cb.closest('label').classList.add('opacity-40');
                } else {
                    cb.disabled = false;
                    cb.closest('label').classList.remove('opacity-40');
                }
            });
        }

        function closeForm() {
            document.getElementById('formModal').classList.add('hidden');
        }

        function playAudio(url) {
            new Audio(url).play().catch(() => {});
        }
    </script>
</x-app-layout>
