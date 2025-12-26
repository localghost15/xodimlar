import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue()],
    root: './assets/vue',
    base: '/build/',
    build: {
        outDir: '../../public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: './assets/vue/main.js',
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name].js',
                assetFileNames: 'assets/[name].[ext]'
            }
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './assets/vue'),
        },
    },
})
