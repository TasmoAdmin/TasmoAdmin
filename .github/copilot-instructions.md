# TasmoAdmin Repository Instructions

TasmoAdmin is a public repository. The application lives in `tasmoadmin/` and uses an existing PHP backend, a Node-based asset pipeline, and the current DDEV and Docker Compose development workflows. Work from that structure. Do not assume this is a monorepo and do not propose framework rewrites when a focused change in the existing codebase will solve the problem.

Prefer the current project layout:

- PHP entry points and page logic in `tasmoadmin/index.php`, `tasmoadmin/pages/`, and `tasmoadmin/includes/`
- Backend classes in `tasmoadmin/src/`
- PHP tests in `tasmoadmin/tests/`
- Frontend JavaScript in `tasmoadmin/resources/js/`
- Frontend styles in `tasmoadmin/resources/scss/`

Use the same validation commands maintainers already run:

```bash
composer install -d tasmoadmin/
cd tasmoadmin && ./vendor/bin/phpstan
cd tasmoadmin && ./vendor/bin/php-cs-fixer fix --dry-run
cd tasmoadmin && ./vendor/bin/phpunit --display-deprecations
cd tasmoadmin && npm ci
cd tasmoadmin && npm run build
cd tasmoadmin && npm run test:js
cd tasmoadmin && npm run prettier:check
```

For issue analysis:

- For bug reports, evaluate whether the issue includes `Expected Behavior`, `Current Behavior`, `Steps to Reproduce`, `Context (Environment)`, and `Context (Device)`.
- For feature requests, anchor analysis on the current `What should we implement` template section and avoid reframing the request as a bug unless the report clearly shows a regression.
- If issue details are incomplete, call out the missing information before proposing implementation work.

For labels:

- Recommend only labels that already exist in this repository.
- For issue work, prefer the current core labels when they fit: `bug`, `enhancement`, `question`, `investigate`, `security`, and `dependencies`.
- Never invent new labels in suggestions.
