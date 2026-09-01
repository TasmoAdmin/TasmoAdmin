import * as esbuild from "esbuild";
import { sassPlugin } from "esbuild-sass-plugin";
import { writeFile, rm, mkdir } from "node:fs/promises";
import path from "node:path";

const scssPaths = ["resources/scss/all.scss"];
const watch = process.env.WATCH_MODE === "true";

// Cleanup before build
if (!watch) {
    await rm("resources/js/compiled/", { recursive: true, force: true });
    await rm("resources/css/compiled/", { recursive: true, force: true });
    await mkdir("resources/js/compiled/", { recursive: true });
    await mkdir("resources/css/compiled/", { recursive: true });
}

const commonOptions = {
    bundle: true,
    metafile: true,
    minify: !watch,
    entryNames: watch ? "[name]" : "[name]-[hash]",
    loader: {
        ".ttf": "file",
        ".otf": "file",
        ".woff2": "file",
        ".woff": "file",
        ".eot": "file",
        ".svg": "file",
    },
};

const sassOptions = {
    quietDeps: true,
    silenceDeprecations: ['import', 'color-functions', 'global-builtin', 'if-function'],
};

async function build() {
    if (watch) {
        const cssContext = await esbuild.context({
            ...commonOptions,
            entryPoints: scssPaths,
            plugins: [sassPlugin(sassOptions)],
            outdir: "resources/css/compiled/",
        });
        await cssContext.watch();

        const jsContext = await esbuild.context({
            ...commonOptions,
            entryPoints: [
                "resources/js/app.js",
                "resources/js/Sonoff.js",
                "resources/js/vendor.js",
                "resources/js/devices.js",
                "resources/js/device_config.js",
                "resources/js/device_update.js",
                "resources/js/start.js",
                "resources/js/toggle_confirmation.js",
                "resources/js/status_helpers.js",
                "resources/js/nightmode.js",
                "resources/js/ip_sort.js",
                "resources/js/device_update_logic.js",
                "resources/js/device_list_preferences.js"
            ],
            outdir: "resources/js/compiled/",
            metafile: true,
        });
        await jsContext.watch();
        console.log("Watching for changes...");
    } else {
        const cssResult = await esbuild.build({
            ...commonOptions,
            entryPoints: scssPaths,
            plugins: [sassPlugin(sassOptions)],
            outdir: "resources/css/compiled/",
        });

        const jsResult = await esbuild.build({
            ...commonOptions,
            entryPoints: [
                "resources/js/app.js",
                "resources/js/Sonoff.js",
                "resources/js/vendor.js",
                "resources/js/devices.js",
                "resources/js/device_config.js",
                "resources/js/device_update.js",
                "resources/js/start.js",
                "resources/js/toggle_confirmation.js",
                "resources/js/status_helpers.js",
                "resources/js/nightmode.js",
                "resources/js/ip_sort.js",
                "resources/js/device_update_logic.js",
                "resources/js/device_list_preferences.js"
            ],
            outdir: "resources/js/compiled/",
            metafile: true,
        });

        // Generate manifest
        const manifest = {};
        const outputs = { ...cssResult.metafile.outputs, ...jsResult.metafile.outputs };

        for (const [key, value] of Object.entries(outputs)) {
            if (value.entryPoint) {
                const entryName = path.basename(value.entryPoint, path.extname(value.entryPoint));
                const outputName = path.basename(key);
                manifest[entryName] = outputName;
            }
        }
        await writeFile("resources/manifest.json", JSON.stringify(manifest, null, 2));
        console.log("Build complete with manifest generation.");
    }
}

await build();
