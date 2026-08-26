import react from '@vitejs/plugin-react'
import { defineConfig } from 'vitest/config'

// separado do vite.config.ts para não interferir com plugins futuros (ex.: PWA)
export default defineConfig({
  plugins: [react()],
  test: { environment: 'jsdom', globals: true },
})
