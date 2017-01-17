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
    mix.sass([
        'font-awesome/font-awesome.scss',
        'app.scss'
    ], 'public/css/main.css');

    mix.webpack('app.js', 'public/js/main.js');
    mix.scripts([
        '../../../public/js/main.js',
        '../../../node_modules/metismenu/dist/metisMenu.min.js'
    ], 'public/js/app.js');

    mix.less([
        'sb-admin-2.less',
        '../../../node_modules/bootstrap-datepicker/build/build_standalone3.less'
    ], 'public/css/third-party.css');

    mix.styles([
        '../../../node_modules/metismenu/dist/metisMenu.min.css',
        '../../../node_modules/toastr/build/toastr.min.css',
        '../../../node_modules/daterangepicker/daterangepicker-bs3.min.css',
        '../../../public/css/third-party.css',
        '../../../public/css/main.css'
    ], 'public/css/app.css');

    mix.copy([
        'resources/assets/fonts'
    ], 'public/build/fonts');

    mix.copy([
        'node_modules/bootstrap/fonts',
    ], 'public/build/fonts/bootstrap');

    mix.version([
        'public/css/app.css',
        'public/js/app.js'
    ]);
});
