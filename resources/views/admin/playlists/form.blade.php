<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-gray-900 dark:text-white">
            {{ $playlist ? 'Edit Playlist' : 'Tambah Playlist' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $playlist ? route('admin.playlists.update', $playlist) : route('admin.playlists.store') }}"
                  class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                @csrf
                @if ($playlist) @method('PUT') @endif

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe</label>
                    <select name="type" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="opening" {{ $playlist?->type === 'opening' ? 'selected' : '' }}>Pembuka (sebelum bell pertama)</option>
                        <option value="closing" {{ $playlist?->type === 'closing' ? 'selected' : '' }}>Penutup (setelah bell terakhir)</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Playlist</label>
                    <input type="text" name="name" value="{{ old('name', $playlist?->name) }}" required maxlength="255"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Audio Assets --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aset Audio (urutkan sesuai keinginan)</label>
                    <div id="audio-list" class="space-y-2">
                        @php $selected = old('audio_assets', $playlist ? array_column($playlist->audio_assets ?? [], 'filename') : []); @endphp
                        @foreach ($audioAssets as $asset)
                            <label class="audio-item flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                   data-filename="{{ $asset->filename }}">
                                <input type="checkbox" name="audio_assets[]" value="{{ $asset->filename }}"
                                       class="audio-checkbox rounded border-gray-300 dark:border-gray-600"
                                       {{ in_array($asset->filename, $selected) ? 'checked' : '' }}>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $asset->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->filename }}</p>
                                </div>
                                <span class="drag-handle text-gray-400 cursor-grab text-lg">⠿</span>
                            </label>
                        @endforeach
                    </div>
                    @error('audio_assets') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Time Range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Mulai</label>
                        <input type="time" name="time_range_start" value="{{ old('time_range_start', $playlist?->time_range_start?->format('H:i')) }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Selesai</label>
                        <input type="time" name="time_range_end" value="{{ old('time_range_end', $playlist?->time_range_end?->format('H:i')) }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                {{-- Day of Week --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hari Berlaku (kosongkan untuk semua hari aktif)</label>
                    <div class="flex flex-wrap gap-3">
                        @php $days = old('day_of_week', $playlist?->day_of_week ?? []); @endphp
                        @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $i => $day)
                            <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="day_of_week[]" value="{{ $i }}"
                                       {{ in_array($i, $days) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600">
                                {{ $day }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Order & Active --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Urutan</label>
                        <input type="number" name="order" value="{{ old('order', $playlist?->order ?? 0) }}" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $playlist?->is_active ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600">
                            Aktif
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                            class="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                        {{ $playlist ? 'Simpan Perubahan' : 'Simpan' }}
                    </button>
                    <a href="{{ route('admin.playlists.index') }}"
                       class="px-6 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Allow drag-to-reorder audio items using native drag/drop
        (function() {
            const list = document.getElementById('audio-list');
            let dragItem = null;

            list.querySelectorAll('.audio-item').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    dragItem = this;
                    this.style.opacity = '0.4';
                });
                item.addEventListener('dragend', function(e) {
                    this.style.opacity = '1';
                    dragItem = null;
                });
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (this !== dragItem) {
                        const rect = this.getBoundingClientRect();
                        const mid = rect.top + rect.height / 2;
                        if (e.clientY < mid) {
                            this.parentNode.insertBefore(dragItem, this);
                        } else {
                            this.parentNode.insertBefore(dragItem, this.nextSibling);
                        }
                    }
                });
                item.setAttribute('draggable', 'true');
            });
        })();
    </script>
</x-app-layout>
