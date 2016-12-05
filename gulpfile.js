const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(mix => {
    mix.sass('app.scss', 'public/css/main.css')
       .webpack('app.js');

    mix.less([
        'sb-admin-2.less'
    ], 'public/css/theme.css');

    mix.styles([
        '../../../node_modules/metismenu/dist/metisMenu.min.css',
        '../../../public/css/main.css',
        '../../../public/css/theme.css'
    ], 'public/css/app.css');

    mix.version('public/css/app.css');
});
