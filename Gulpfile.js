var elixir = require('laravel-elixir');

elixir.config.assetsPath = 'src/assets';
elixir.config.production = true;
elixir.config.css.sass.pluginOptions.outputStyle = 'compressed';

require('laravel-elixir-eslint');
require('laravel-elixir-browserify-official');

elixir(function(mix) {

    mix.browserify('admin.js');

    mix
     .sass('admin.scss')
     .sass('tinymce.scss');
});
