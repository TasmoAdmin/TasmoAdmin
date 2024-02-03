import * as esbuild from "esbuild";
import {sassPlugin} from 'esbuild-sass-plugin'


const scssPaths = ["resources/scss/all.scss"];

const jsPaths = ["resources/js/*.js"];

async function buildCSS() {
  await esbuild
    .build({
      entryPoints: scssPaths,
      bundle: true,
      outfile: "resources/css/compiled/all.css",
      loader: {
        ".ttf": "file",
        ".otf": "file",
        ".svg": "file",
        ".eot": "file",
        ".woff": "file",
        ".woff2": "file",
      },
      plugins: [sassPlugin()],
    })
    .then(() => console.log("⚡ CSS Build complete! ⚡"))
    .catch(() => process.exit(1));
}

async function buildJS() {
  await esbuild
    .build({
      entryPoints: jsPaths,
      bundle: true,
      outdir: "resources/js/compiled/",
    })
    .then(() => console.log("⚡ JS Build complete! ⚡"))
    .catch(() => process.exit(1));
}

await buildCSS();
await buildJS();
