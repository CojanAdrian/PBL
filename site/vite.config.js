import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { cpSync } from 'node:fs';

// Country flags for the dashboard's visitor widgets, served from public/images/flags.
const flags = {
    name: 'copy-flags',
    buildStart() {
        cpSync('node_modules/flag-icons/flags/4x3', 'public/images/flags', { recursive: true });
    },
};

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
        tailwindcss(),
        flags,
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
