const path = require('path');
const isProd = process.env.NODE_ENV === 'production';

module.exports = {
    // Compile entry.js
    scripts: [
        {
            entry: path.resolve(__dirname, 'src/entry.js'),
            dest: path.resolve(__dirname, 'dist/output.js'),
            options: {
                sourceMap: isProd ? false : 'inline',
                moduleName: 'myModule',
            },
        },
    ],
    // Compile entry.scss
    styles: [
        {
            entry: path.resolve(__dirname, 'src/entry.scss'),
            dest: path.resolve(__dirname, 'dist/output.css'),
            options: {
                sourceMap: isProd ? 'file' : true,
                minify: true,
            },
        },
    ],
    // Copy files from src to dest
    copy: [
        {
            from: path.resolve(__dirname, 'src/assets'),
            to: path.resolve(__dirname, 'dist/assets'),
        },
    ],
    // Watcher
    watch: {
        handlers: [{
                pattern: `./src/*.scss`,
                events: [{
                    on: 'change',
                    name: 'watch styles',
                    command: './node_modules/.bin/ecl-builder styles',
                    message: 'Styles updated',
                    reload: 'dist/*.css'
                }, ],
            },
            {
                pattern: `./src/*.js`,
                events: [{
                    on: 'change',
                    name: 'watch scripts',
                    command: './node_modules/.bin/ecl-builder scripts',
                    message: 'Scripts updated',
                    reload: 'dist/*.js'
                }, ],
            },
            {
                pattern: `./assets/**/*.*`,
                events: [{
                        on: 'change',
                        name: 'watch copy',
                        command: './node_modules/.bin/ecl-builder copy',
                        message: 'Updated assets',
                        reload: 'dist/assets/*'
                    },
                    {
                        on: 'add',
                        name: 'watch copy',
                        command: './node_modules/.bin/ecl-builder copy',
                        message: 'New assets added',
                        reload: 'dist/assets/*'
                    },
                ],
            }
        ]
    }
};
