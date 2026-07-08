<?php

namespace App\Http\Controllers\Admin;

use App\Events\ScheduleUpdated;
use App\Http\Controllers\Controller;
use App\Models\AudioAsset;
use App\Models\BellPlaylist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BellPlaylistController extends Controller
{
    public function index()
    {
        $playlists = BellPlaylist::orderBy('type')->orderBy('order')->get();
        return view('admin.playlists.index', compact('playlists'));
    }

    public function create()
    {
        $audioAssets = AudioAsset::orderBy('name')->get();
        return view('admin.playlists.form', [
            'playlist' => null,
            'audioAssets' => $audioAssets,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);
        BellPlaylist::create($data);

        broadcast(new ScheduleUpdated);

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist berhasil ditambahkan.');
    }

    public function edit(BellPlaylist $playlist)
    {
        $audioAssets = AudioAsset::orderBy('name')->get();
        return view('admin.playlists.form', compact('playlist', 'audioAssets'));
    }

    public function update(Request $request, BellPlaylist $playlist)
    {
        $data = $this->validateInput($request, $playlist->id);
        $playlist->update($data);

        broadcast(new ScheduleUpdated);

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist berhasil diperbarui.');
    }

    public function destroy(BellPlaylist $playlist)
    {
        $playlist->delete();

        broadcast(new ScheduleUpdated);

        return redirect()->route('admin.playlists.index')
            ->with('success', 'Playlist berhasil dihapus.');
    }

    private function validateInput(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'type' => ['required', Rule::in(['opening', 'closing'])],
            'name' => 'required|string|max:255',
            'audio_assets' => 'required|array|min:1',
            'audio_assets.*' => 'required|string',
            'time_range_start' => 'nullable|date_format:H:i',
            'time_range_end' => 'nullable|date_format:H:i',
            'is_active' => 'sometimes|boolean',
            'day_of_week' => 'nullable|array',
            'day_of_week.*' => 'integer|between:0,6',
            'action_after' => 'nullable|string|max:255',
            'action_after_delay' => 'nullable|integer|min:0',
            'custom_command' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ];

        $validated = $request->validate($rules);

        $validated['audio_assets'] = $this->resolveAudioAssets($validated['audio_assets']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['action_after_delay'] = (int) ($validated['action_after_delay'] ?? 0);
        $validated['order'] = (int) ($validated['order'] ?? 0);

        return $validated;
    }

    private function resolveAudioAssets(array $filenames): array
    {
        $assets = AudioAsset::whereIn('filename', $filenames)->get()->keyBy('filename');
        $result = [];
        foreach ($filenames as $filename) {
            if (isset($assets[$filename])) {
                $result[] = [
                    'asset_id' => $assets[$filename]->id,
                    'filename' => $assets[$filename]->filename,
                    'name' => $assets[$filename]->name,
                ];
            }
        }
        return $result;
    }
}
