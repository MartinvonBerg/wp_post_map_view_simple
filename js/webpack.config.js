// webpack.config.js is the configuration file for webpack. It is a JavaScript file that exports an object with configuration options.

const path = require('path');
let _mode = 'production';

// create bundle for fotorama
module.exports = [
{
  target: ['web','es2017'],
  entry: {
    main: './js/pmtv_main.js',
  },
  resolve: {
    fallback: {
      "path": require.resolve("path-browserify")
    }
  },
  output: {
    filename: 'pmtv_[name].js',
    chunkFilename: 'pmtv_[name].js',
    path: path.resolve(__dirname, '../build'),
  },
  mode: _mode, 
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif)$/i,
        type: 'asset/resource', 
      },
    ],
  },
}];