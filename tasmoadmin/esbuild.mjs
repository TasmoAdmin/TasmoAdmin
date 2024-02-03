import * as esbuild from "esbuild";
import { sassPlugin } from "esbuild-sass-plugin";

const scssPaths = ["resources/scss/all.scss"];

const jsPaths = ["resources/js/*.js"];

const watch = false;

async function buildCSS() {
  const options = {
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
  };

  if (watch) {
    const ctx = await esbuild.context(options);
    await ctx.watch();
    console.log("⚡ CSS Watching! ⚡");
    return;
  }

  await esbuild.build(options);
  console.log("⚡ CSS Build complete! ⚡");
}

async function buildJS() {
  const options = {
    entryPoints: jsPaths,
    bundle: true,
    outdir: "resources/js/compiled/",
  };

  if (watch) {
    const ctx = await esbuild.context(options);
    await ctx.watch();
    console.log("⚡ JS Watching! ⚡");
    return;
  }

  await esbuild.build(options);
  console.log("⚡ JS Build complete! ⚡");
}

await buildCSS();
await buildJS();
