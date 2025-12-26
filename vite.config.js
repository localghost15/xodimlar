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
            input: '/home/pmsd/.gemini/antigravity/scratch/xodimlar/assets/vue/main.js',
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './assets/vue'),
        },
    },
})
