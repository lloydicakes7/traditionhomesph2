const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/client.scss', 'public/css')
    .sass('resources/sass/reports.scss', 'public/css');
