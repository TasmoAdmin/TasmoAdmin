const { readFile, writeFile } = require("node:fs/promises");
const glob = require("glob");
const minify = require("@node-minify/core");
const terser = require("@node-minify/terser");
const cleanCss = require("@node-minify/clean-css");

async function main() {
  const jsFiles = await glob.glob("resources/js/compiled/*.js");
  for (const file of jsFiles) {
    if (file.includes(".min.js")) {
      continue;
    }
    console.log(`processing ${file}`);
    const content = await readFile(file, "utf8");
    const minified = await minify({
      compressor: terser,
      content,
    });
    await writeFile(file.slice(0, -3) + ".min.js", minified);
  }

  const cssFiles = await glob.glob("resources/css/compiled/*.css");
  for (const file of cssFiles) {
    if (file.includes(".min.css")) {
      continue;
    }

    console.log(`processing ${file}`);
    const content = await readFile(file, "utf8");
    const minified = await minify({
      compressor: cleanCss,
      content,
    });
    await writeFile(file.slice(0, -4) + ".min.css", minified);
  }
}

console.log("Minifying resources");

main();
