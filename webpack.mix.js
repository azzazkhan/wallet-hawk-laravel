const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .options({
    // `public` will be prepended automatically
    fileLoaderDirs: {
      fonts: "dist/fonts",
      images: "dist/images",
    },
    hmrOptions: {
      host: "localhost",
      port: "8080",
    },
  })
  .ts("resources/ts/index.ts", "public/dist/js/app.min.js")
  .postCss("resources/css/tailwind.css", "public/dist/css/tailwind.min.css", [
    require("tailwindcss"),
  ])
  .sass("resources/scss/app.scss", "public/dist/css/style.min.css", undefined, [
    require("tailwindcss"),
  ])
  .extract() // Extract vendor libraries into separate bundle which can be cached
  .disableNotifications();

if (mix.inProduction()) {
  mix.version(); // Enable cache busting
} else {
  // Enable live reloading and HMR in development
  mix.sourceMaps().browserSync({
    proxy: "localhost:8080",
    open: false,
  });
}
