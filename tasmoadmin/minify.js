const glob = require('glob');
const minify = require('@node-minify/core');
const terser = require('@node-minify/terser');
const cleanCSS = require('@node-minify/clean-css');


async function main()
{
    const jsFiles = await glob.glob('resources/js/compiled/*.js')
    for (const file of jsFiles) {

        if (file.includes('.min.js')) {
            continue;
        }
        console.log(`processing ${file}`);
        minify({
            compressor: terser,
            input: file,
            output: file.slice(0, -3) + '.min.js',
        });
    }

    const cssFiles = await glob.glob('resources/css/compiled/*.css');
    for (const file of cssFiles) {
        if (file.includes('.min.css')) {
            continue
        }

        console.log(`processing ${file}`);
        minify({
            compressor: cleanCSS,
            input: file,
            output: file.slice(0, -4) + '.min.css',
        });
    }
}

main();
