import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import {viteStaticCopy} from 'vite-plugin-static-copy';

export default defineConfig({
  plugins: [
    laravel({
      input: [
              'resources/scss/app.scss',
              'resources/scss/auth.scss',
              'resources/scss/icons.scss',
              'resources/scss/fonts.scss',
              'resources/scss/landing.scss',
              'resources/js/app.js'
            ],
      refresh: true,
    }),
    viteStaticCopy({
      targets: [
        {
          src: 'resources/images',
          dest: './../',
        }
      ]
    })
  ],
});
