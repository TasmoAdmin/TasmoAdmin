# Repository Guidelines

## Project Structure & Module Organization
The application lives in `tasmoadmin/`. Keep PHP entry points and page logic in `index.php`, `includes/`, and `pages/`. Put backend classes in `tasmoadmin/src/` under `TasmoAdmin\\`, with matching PHPUnit coverage in `tasmoadmin/tests/`. Frontend sources live in `tasmoadmin/resources/js/` and `tasmoadmin/resources/scss/`; built CSS lands in `tasmoadmin/resources/css/`. Do not hand-edit `tasmoadmin/data/`, `tasmoadmin/tmp/`, `vendor/`, or `node_modules/`.

## Environment & Tooling
Prefer the existing DDEV workflow. Use `ddev exec` for reproducible PHP and Node commands, and use `rg` for code search. The main toolchain is PHP 8.2+, Composer, npm, PHPUnit, PHPStan, `php-cs-fixer`, Prettier, and local `pre-commit` hooks.

## Build, Test, and Development Commands
Run commands from the repository root:

- `ddev start` boots the local web environment.
- `ddev install-deps` installs Composer and npm dependencies inside `tasmoadmin/`.
- `ddev build-assets` rebuilds frontend bundles after JS or SCSS changes.
- `ddev qa` runs the Composer quality suite.
- `ddev exec npm run test:js` runs the Node test suite.
- `pre-commit run --all-files` runs the hook set before pushing.

Direct app-level commands are also valid in `tasmoadmin/`: `./vendor/bin/phpunit --display-deprecations`, `./vendor/bin/phpstan`, `./vendor/bin/php-cs-fixer fix --dry-run`, `npm run build`, and `npm run prettier:check`.

## Coding Style & Naming Conventions
PHP uses PSR-12 via `php-cs-fixer` and keeps `declare(strict_types=1);` at the top of classes. Follow existing names such as `DeviceRepository`, `HttpClientFactory`, and `*Helper`; test classes end in `Test`. JavaScript uses Prettier formatting, double quotes, and small module-style helpers. Edit `resources/js/` and `resources/scss/`, then rebuild assets.

## Testing Guidelines
PHPUnit covers `tasmoadmin/src/`; pair backend work with targeted tests in `tasmoadmin/tests/`. Use `tests/Page/` for rendered markup and `tests/js/**/*.test.js` for frontend logic. Run the narrowest relevant suite while iterating, then finish with PHPStan, PHPUnit, JS tests, Prettier, and a frontend build.

## Commit & Pull Request Guidelines
Recent history favors short, imperative subjects, often with prefixes like `feat:`, `fix:`, `test:`, and `style:`. Keep commits scoped to one concern. PRs should target `master`, summarize user-visible changes, link issues when applicable, and list the validation commands you ran. Include screenshots for UI changes.

## Agent-Specific Notes
Never edit `vendor/`, `node_modules/`, or runtime caches to “fix” behavior. Keep changes narrow, follow the existing PHP/Bootstrap/jQuery patterns, and prefer source edits over rewrites. Update this file when contributor workflow changes materially.
Always use translation keys for user-facing labels, headings, table columns, and messages; add missing entries to the language files instead of introducing hardcoded strings.
For device configuration UI work, prefer the shared card layout used in the config tabs (`.device-config-card` / `.device-config-timer-card`) instead of loose standalone form blocks.
For frontend or styling changes, verify both day mode and night mode and keep shared styles compatible with both before considering the UI done.
When adding cards, use the shared global night-mode card rules instead of page-specific dark-mode overrides.

## Security & Configuration Tips
Do not commit secrets or real device data. Review `SECURITY.md` before changing auth, update, MQTT, or device-credential paths. For local work, prefer DDEV and repository scripts over ad hoc environment changes.
