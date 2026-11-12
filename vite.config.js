import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
// @feature ui:vue
import vue from '@vitejs/plugin-vue';
// @endfeature
// @feature ui:react
import react from '@vitejs/plugin-react';
// @endfeature

export default defineConfig({
    plugins: [
        laravel({
            // @feature ui:livewire
            input: ['resources/css/app.css'],
            // @endfeature
            // @feature ui:vue
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // @endfeature
            // @feature ui:react
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            // @endfeature
            refresh: true,
        }),
        tailwindcss(),
        // @feature ui:vue
        vue({
            template: {
                transformAssetUrls: { base: null, includeAbsolute: false },
            },
        }),
        // @endfeature
        // @feature ui:react
        react(),
        // @endfeature
    ],
});
