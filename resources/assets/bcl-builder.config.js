const path = require('path');
const pkg = require('./package.json');

const isProd = process.env.NODE_ENV === 'production';
const outputFolder = path.resolve(__dirname);
const nodeModules = path.resolve(__dirname, "./node_modules");

// SCSS includePaths
const includePaths = [nodeModules];

const banner = `${pkg.name} - ${
  pkg.version
} Built on ${new Date().toISOString()}`;

module.exports = {
  scripts: [],
  styles: [
    {
      entry: path.resolve(__dirname, 'sass/style.scss'),
      dest: path.resolve(outputFolder, 'css/style.min.css'),
      options: {
        includePaths,
        minify: true,
        sourceMap: isProd ? 'file' : true,
      },
    },
    {
      entry: path.resolve(__dirname, 'sass/style.scss'),
      dest: path.resolve(outputFolder, 'css/style.min.css'),
      options: {
        includePaths,
        minify: true,
        sourceMap: isProd ? 'file' : true,
      },
    },
  ],
  copy: []
};
