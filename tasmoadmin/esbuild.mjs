import * as esbuild from "esbuild";

await esbuild
  .build({
    entryPoints: ["resources/css/all.css"],
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

await esbuild
  .build({
    entryPoints: ["resources/js/*.js"],
    bundle: true,
    outdir: "resources/js/compiled/",
  })
  .then(() => console.log("⚡ JS Build complete! ⚡"))
  .catch(() => process.exit(1));
