import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: true, // Pozwala na dostęp z zewnątrz kontenera
    port: 3000,
    watch: {
      usePolling: true, // Wymagane dla Dockera na Windows
    },
    proxy: {
      '/api': {
        target: 'http://nginx:80', // W Dockerze używamy nazwy serwisu nginx
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: '../../../../public/frontend',
    emptyOutDir: true,
  }
})