const glob = require('glob');
const minify = require('@node-minify/core');
const terser = require('@node-minify/terser');
const cleanCSS = require('@node-minify/clean-css');

glob('resources/js/*.js', (err, files) => {
    if (err) {
        console.log(err);
        return;
    }

    files.forEach((file) => {
        console.log(`processing ${file}`);
        minify({
            compressor: terser,
            input: file,
            output: file.slice(0, -3) + '.min.js',
        });
    })
});

glob('resources/css/*.css', (err, files) => {
    if (err) {
        console.log(err);
        return;
    }

    files.forEach((file) => {
        console.log(`processing ${file}`);
        minify({
            compressor: cleanCSS,
            input: file,
            output: file.slice(0, -4) + '.min.css',
        });
    })
});

