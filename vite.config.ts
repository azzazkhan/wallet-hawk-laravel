import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tsConfigPaths from 'vite-tsconfig-paths';

export default defineConfig({
    server: {
        hmr: {
            host: '127.0.0.1'
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/tailwind.css',
                'resources/scss/app.scss',
                'resources/ts/main.ts',
                'resources/ts/index.tsx'
            ],
            refresh: true
        }),
        react({
            fastRefresh: true,
            include: 'resources/ts/index.tsx'
        }),
        tsConfigPaths()
    ]
});
