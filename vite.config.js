import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const appRoot = fileURLToPath(new URL('./v1.23.0/', import.meta.url))

export default defineConfig({
  root: appRoot,
  base: './',
  publicDir: false,
  plugins: [vue()],
  server: {
    open: '/dashboard/project-management-vue.html',
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: fileURLToPath(new URL('./v1.23.0/dashboard/project-management-vue.html', import.meta.url)),
    },
  },
})
