import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Helper to get meta tag content
const getMeta = (name: string) => {
    return document.querySelector(`meta[name="${name}"]`)?.getAttribute('content');
};

const reverbKey = getMeta('reverb-key') || import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = getMeta('reverb-host') || import.meta.env.VITE_REVERB_HOST || 'localhost';
const reverbPort = getMeta('reverb-port') || import.meta.env.VITE_REVERB_PORT || 8080;
const reverbScheme = getMeta('reverb-scheme') || import.meta.env.VITE_REVERB_SCHEME || 'http';
const appDebug = getMeta('app-debug') === 'true';

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    if (appDebug) {
        window.Pusher.logToConsole = true;
    }
} else {
    console.error('WebSocket configuration is missing. Echo not initialized.');
}
