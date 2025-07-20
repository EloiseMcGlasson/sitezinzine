const Encore = require('@symfony/webpack-encore');
const CopyPlugin = require('copy-webpack-plugin'); // ← ajoute le plugin
const path = require('path'); // ← utilisé pour les chemins absolus

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/app.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = '3.38';
  })

  // 🔽 Ajoute la copie des fichiers nécessaires à TinyMCE
  .addPlugin(
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, 'node_modules/tinymce/skins'),
          to: 'skins',
        },
        {
          from: path.resolve(__dirname, 'node_modules/tinymce/icons'),
          to: 'icons',
        },
        {
          from: path.resolve(__dirname, 'node_modules/tinymce/themes'),
          to: 'themes',
        },
        {
          from: path.resolve(__dirname, 'node_modules/tinymce/plugins'),
          to: 'plugins',
        },
        {
      from: path.resolve(__dirname, 'node_modules/tinymce/models'),
      to: 'models'
    }
      ],
    })
  )
;

module.exports = Encore.getWebpackConfig();
