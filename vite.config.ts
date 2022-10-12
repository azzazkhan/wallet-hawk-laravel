import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tsConfigPaths from 'vite-tsconfig-paths';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/tailwind.css',
                'resources/scss/app.scss',
                'resources/ts/main.ts'
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
