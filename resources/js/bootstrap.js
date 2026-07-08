import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const cfg = window.__REVERB_CONFIG;

if (cfg) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: cfg.key ?? 'fallback-key',
        wsHost: cfg.host ?? 'localhost',
        wsPort: cfg.port ?? 8081,
        wssPort: cfg.port ?? 443,
        forceTLS: (cfg.scheme ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    document.addEventListener('DOMContentLoaded', () => {
        console.log('[Echo] Connected to Reverb channel bell');
        window.Echo.channel('bell')
            .listen('BellPlayed', (e) => {
                console.log('[Echo] BellPlayed received:', e);
                if (typeof window.playBell === 'function') {
                    window.playBell(e.audio_file);
                }
                if (typeof window.updateScheduleLabels === 'function') {
                    window.updateScheduleLabels();
                }
                if (typeof window.updateSchoolStatus === 'function') {
                    window.updateSchoolStatus();
                }
            })
            .listen('ScheduleUpdated', (e) => {
                console.log('[Echo] ScheduleUpdated received — reloading');
                window.location.reload();
            })
            .listen('EmergencyBellTriggered', (e) => {
                console.log('[Echo] EmergencyBellTriggered received:', e);
                if (typeof window.playBell === 'function') {
                    window.playBell(e.audio_file);
                }
            })
            .listen('PlaylistStarted', (e) => {
                console.log('[Echo] PlaylistStarted received:', e);
                if (typeof window.handlePlaylistStarted === 'function') {
                    window.handlePlaylistStarted(e);
                }
            })
            .listen('PlaylistFinished', (e) => {
                console.log('[Echo] PlaylistFinished received:', e);
                if (typeof window.handlePlaylistFinished === 'function') {
                    window.handlePlaylistFinished(e);
                }
            });
    });
} else {
    console.warn('[Echo] No REVERB_CONFIG found');
}
