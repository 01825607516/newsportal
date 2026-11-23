import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'v1.23.0/src/project-management.js'],
      refresh: true,
    }),
    vue(),
  ],
  build: {
    manifest: 'manifest.json',
  },
})
