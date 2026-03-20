import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/currency-fieldtype.js',
                // 'resources/css/currency-fieldtype.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
});
