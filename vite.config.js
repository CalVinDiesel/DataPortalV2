import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import cesium from 'vite-plugin-cesium';

import path from 'path';

export default defineConfig(({ command }) => ({
    resolve: {
        alias: {
            'cesium': path.resolve(__dirname, 'resources/js/viewer/cesium-wrapper.js')
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/viewer/main.tsx'
            ],
            refresh: true,
        }),
        react(),
        cesium({
            // Fix absolute CORS origin paths for Laravel Dev Server vs Vite Dev Server
            cesiumBaseUrl: command === 'serve' ? 'http://127.0.0.1:5176/cesium/' : '/build/cesium/'
        }),
    ],
    server: {
        host: '127.0.0.1',
        port: 5176,
        strictPort: true,
        proxy: {
            '/proxy': {
                target: 'http://127.0.0.1',
                changeOrigin: true,
                router: (req) => {
                    const url = new URL(req.url, 'http://127.0.0.1').searchParams.get('url');
                    return url ? new URL(url).origin : undefined;
                },
                rewrite: (path) => {
                    const url = new URL(path, 'http://127.0.0.1').searchParams.get('url');
                    if (url) {
                        const target = new URL(url);
                        return target.pathname + target.search;
                    }
                    return path;
                }
            }
        }
    }
}));
