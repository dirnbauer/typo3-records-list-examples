#!/usr/bin/env bash
#
# runTests.sh — unified entry point for local and CI test runs.
#
# The script only depends on composer-installed binaries in vendor/bin/, so it
# works in any environment (DDEV, bare host, CI).
#
# Usage:
#   Build/Scripts/runTests.sh -s <suite> [-p <php>]
#
#   Suites: lint | unit | functional | phpstan | cgl | composer | audit | ci

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

cd "${PROJECT_ROOT}"

SUITE=""
PHP_VERSION=""
PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-1G}"

usage() {
    cat <<'USAGE'
Usage: Build/Scripts/runTests.sh -s <suite> [-p <php>]

Suites:
  lint        PHP syntax check and XLIFF well-formedness.
  unit        Unit test suite (TSconfig presets, label catalog, template contract).
  functional  Functional test suite; renders every example view. SQLite by
              default, or export the typo3Database* variables for MariaDB.
  phpstan     Static analysis at PHPStan level 8 with strict rules.
  cgl         PHP-CS-Fixer dry run with the TYPO3 coding standards.
  composer    composer validate --strict (no lock file is committed).
  audit       composer audit; advisories are reported, CI does not block on them.
  ci          composer, lint, cgl, phpstan and unit (functional needs a database).

Options:
  -p <php>    Informational only: PHP version the suite is expected to run on
              (e.g. 8.4, 8.5). The script uses whatever `php` resolves to in PATH.
  -h          Show this help.

Environment:
  PHP_MEMORY_LIMIT  Memory limit for PHPUnit runs (default: 1G).
USAGE
}

while getopts "s:p:h" opt; do
    case "${opt}" in
        s) SUITE="${OPTARG}" ;;
        p) PHP_VERSION="${OPTARG}" ;;
        h) usage; exit 0 ;;
        *) usage; exit 64 ;;
    esac
done

if [[ -z "${SUITE}" ]]; then
    usage
    exit 64
fi

if [[ -n "${PHP_VERSION}" ]]; then
    echo "# Target PHP version: ${PHP_VERSION} (informational)"
fi

run_lint() {
    local failed=0 output
    while IFS= read -r -d '' file; do
        if ! output=$(php -l "${file}" 2>&1); then
            echo "${output}" >&2
            failed=1
        fi
    done < <(find Tests -name '*.php' -print0; find . -maxdepth 1 -name '.php-cs-fixer.dist.php' -print0)
    xmllint --noout Resources/Private/Language/*.xlf
    [[ "${failed}" -eq 0 ]]
}

run_unit() {
    php -d memory_limit="${PHP_MEMORY_LIMIT}" vendor/bin/phpunit -c Build/phpunit/UnitTests.xml
}

run_functional() {
    php -d memory_limit="${PHP_MEMORY_LIMIT}" vendor/bin/phpunit -c Build/phpunit/FunctionalTests.xml
}

run_phpstan() {
    vendor/bin/phpstan analyse --no-progress --memory-limit=512M
}

run_cgl() {
    vendor/bin/php-cs-fixer fix --dry-run --diff
}

run_composer() {
    composer validate --strict --no-check-lock
}

run_audit() {
    composer audit --abandoned=report
}

case "${SUITE}" in
    lint)       run_lint ;;
    unit)       run_unit ;;
    functional) run_functional ;;
    phpstan)    run_phpstan ;;
    cgl)        run_cgl ;;
    composer)   run_composer ;;
    audit)      run_audit ;;
    ci)
        run_composer
        run_lint
        run_cgl
        run_phpstan
        run_unit
        ;;
    *)
        echo "Unknown suite: ${SUITE}" >&2
        usage
        exit 64
        ;;
esac
