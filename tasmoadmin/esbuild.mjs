import * as esbuild from "esbuild";
import { sassPlugin } from "esbuild-sass-plugin";
import { fileURLToPath } from "node:url";

const scssPaths = ["resources/scss/all.scss"];

const jsPaths = ["resources/js/*.js"];

const watch = process.env.WATCH_MODE || false;

function shouldIgnoreSassWarning(message, opts = {}) {
  const warningPath = opts.span?.url?.pathname ?? "";

  if (
    message.startsWith("Found no color leading to 4.5:1 contrast ratio") ||
    message.includes("repetitive deprecation warnings omitted.")
  ) {
    return true;
  }

  if (
    warningPath.includes("/node_modules/bootstrap/") &&
    message.startsWith("The Sass if() syntax is deprecated")
  ) {
    return true;
  }

  return false;
}

function getSassWarningPath(url) {
  if (!url) {
    return "";
  }

  return url.protocol === "file:"
    ? fileURLToPath(url)
    : (url.pathname ?? String(url));
}

function logSassWarning(message, opts = {}) {
  const lines = [`sass warning: ${message}`];

  if (opts.span?.url) {
    lines.push(
      "",
      `${getSassWarningPath(opts.span.url)}:${opts.span.start.line}:${opts.span.start.column}`,
    );
  }

  if (opts.span?.text) {
    lines.push(opts.span.text);
  }

  if (opts.stack) {
    lines.push("", opts.stack);
  }

  console.warn(lines.join("\n"));
}

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
    plugins: [
      sassPlugin({
        quietDeps: true,
        silenceDeprecations: ["color-functions", "global-builtin", "import"],
        logger: {
          warn(message, opts) {
            if (shouldIgnoreSassWarning(message, opts)) {
              return;
            }

            logSassWarning(message, opts);
          },
        },
      }),
    ],
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
