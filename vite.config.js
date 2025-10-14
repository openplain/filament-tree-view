import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'resources/dist',
    emptyOutDir: false,
    rollupOptions: {
      input: 'resources/css/tree-view.css',
      output: {
        assetFileNames: 'filament-tree-view.css',
      },
    },
  },
});
