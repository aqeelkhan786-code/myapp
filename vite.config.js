import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Listen on all interfaces (needed for ngrok)
        port: 5173,
        strictPort: true,
        hmr: {
            // Use ngrok domain if provided, otherwise use localhost
            host: process.env.VITE_HMR_HOST || 'localhost',
            protocol: 'ws',
        },
        cors: {
            origin: '*', // Allow all origins (for ngrok)
            credentials: true,
        },
    },
});
