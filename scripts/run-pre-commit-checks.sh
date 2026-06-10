#!/usr/bin/env bash
set -euo pipefail

repo_root="$(git rev-parse --show-toplevel)"
cd "$repo_root"

mapfile -d '' staged_files < <(git diff --cached --name-only --diff-filter=ACMR -z)

if [ ${#staged_files[@]} -eq 0 ]; then
    exit 0
fi

if ! command -v ddev >/dev/null 2>&1; then
    echo "[pre-commit] ddev is required for local checks."
    exit 1
fi

if ! ddev exec true >/dev/null 2>&1; then
    echo "[pre-commit] ddev is not running. Start it with 'ddev start' and try again."
    exit 1
fi

matches_staged() {
    local pattern="$1"
    local file

    for file in "${staged_files[@]}"; do
        if [[ "$file" =~ $pattern ]]; then
            return 0
        fi
    done

    return 1
}

run_in_tasmoadmin() {
    local description="$1"
    shift

    echo "[pre-commit] $description"
    ddev exec bash -lc "cd /var/www/html/tasmoadmin && $*"
}

php_pattern='^tasmoadmin/.*\.php$|^tasmoadmin/(composer\.json|composer\.lock|phpstan\.neon\.dist|phpunit\.xml\.dist|\.php-cs-fixer\.dist\.php)$'
frontend_pattern='^tasmoadmin/(resources/js/|resources/scss/|tests/js/)|^tasmoadmin/(package\.json|package-lock\.json|esbuild\.mjs|minify\.js)$'

if matches_staged "$php_pattern"; then
    run_in_tasmoadmin "Running php-cs-fixer" "./vendor/bin/php-cs-fixer fix --dry-run --allow-unsupported-php-version=yes"
    run_in_tasmoadmin "Running phpstan" "./vendor/bin/phpstan"
    run_in_tasmoadmin "Running phpunit" "./vendor/bin/phpunit --display-deprecations"
fi

if matches_staged "$frontend_pattern"; then
    run_in_tasmoadmin "Running prettier:check" "npm run prettier:check"
    run_in_tasmoadmin "Running JS tests" "npm run test:js"
    run_in_tasmoadmin "Running asset build" "npm run build"
fi
