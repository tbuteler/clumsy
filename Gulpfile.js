var elixir = require('laravel-elixir');

elixir.config.assetsPath = 'src/assets';
elixir.config.production = false;
elixir.config.css.sass.pluginOptions.outputStyle = 'compressed';

require('laravel-elixir-eslint');
require('laravel-elixir-browserify-official');

elixir(function(mix) {

    mix
     .eslint('admin.js')
     .browserify('admin.js')
     .sass('admin.scss')
     .sass('tinymce.scss');
});
