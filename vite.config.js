import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  root: './js',
  build: {
    outDir: '../public/resources',
    emptyOutDir: true,
    rollupOptions: {
      input: './js/app.js',
      output: {
        assetFileNames: 'app.[ext]',
        entryFileNames: 'app.js',
      },
    },
  },
  plugins: [tailwindcss()],
});
