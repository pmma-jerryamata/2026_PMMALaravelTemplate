import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            //input: ['resources/css/app.css', 'resources/js/app.js', 'resources/sass/app.scss', 'public/css'],
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/apptech.js'],
            refresh: true,
        }),
    ],
    server:{
        host: 'localhost',
        hmr: {
            host: 'localhost',
        }
    },
});
