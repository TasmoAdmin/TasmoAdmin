import * as esbuild from "esbuild";

const cssPaths = ["resources/css/all.css"];

const jsPaths = ["resources/js/*.js"];

async function buildCSS() {
  await esbuild
    .build({
      entryPoints: cssPaths,
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
