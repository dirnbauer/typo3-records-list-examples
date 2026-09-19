#!/usr/bin/env bash
#
# runTests.sh: one entry point for local and CI checks. It only needs the
# composer-installed binaries in vendor/bin, so it runs the same everywhere.
#
#   Build/Scripts/runTests.sh -s <suite>
#
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/../.."

PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-1G}"

usage() {
    cat <<'USAGE'
Usage: Build/Scripts/runTests.sh -s <suite>

Suites:
  lint        PHP syntax and XLIFF well-formedness.
  cgl         PHP-CS-Fixer dry run with the TYPO3 coding standards.
  cglfix      PHP-CS-Fixer, applies the fixes.
  phpstan     PHPStan level 8 with strict rules.
  unit        Unit tests: TSconfig, labels, templates, stylesheets.
  functional  Functional tests: every example view renders through the Records
              module. SQLite by default; export typo3Database* for MariaDB.
  composer    composer validate --strict (no lock file is committed).
  audit       composer audit; CI reports advisories without blocking.
  ci          composer, lint, cgl, phpstan and unit (functional needs a database).

Environment:
  PHP_MEMORY_LIMIT  Memory limit for PHPUnit (default: 1G).
USAGE
}

SUITE=""
while getopts "s:h" opt; do
    case "${opt}" in
        s) SUITE="${OPTARG}" ;;
        h) usage; exit 0 ;;
        *) usage; exit 64 ;;
    esac
done
[[ -n "${SUITE}" ]] || { usage; exit 64; }

run_lint() {
    local failed=0 output
    while IFS= read -r -d '' file; do
        if ! output=$(php -l "${file}" 2>&1); then
            echo "${output}" >&2
            failed=1
        fi
    done < <(find Tests -name '*.php' -print0; find . -maxdepth 1 -name '.php-cs-fixer.dist.php' -print0)
    # XLIFF well-formedness with PHP's DOM extension: GitHub runners ship no xmllint.
    php -r '
        $failed = 0;
        libxml_use_internal_errors(true);
        foreach (glob("Resources/Private/Language/*.xlf") as $file) {
            if (!(new DOMDocument())->load($file)) {
                foreach (libxml_get_errors() as $error) {
                    fwrite(STDERR, sprintf("%s:%d %s", $file, $error->line, $error->message));
                }
                libxml_clear_errors();
                $failed = 1;
            }
        }
        exit($failed);
    ' || failed=1
    [[ "${failed}" -eq 0 ]]
}

run_phpunit() {
    php -d memory_limit="${PHP_MEMORY_LIMIT}" vendor/bin/phpunit -c "Build/phpunit/$1.xml"
}

case "${SUITE}" in
    lint)       run_lint ;;
    cgl)        vendor/bin/php-cs-fixer fix --dry-run --diff ;;
    cglfix)     vendor/bin/php-cs-fixer fix ;;
    phpstan)    vendor/bin/phpstan analyse --no-progress --memory-limit=512M ;;
    unit)       run_phpunit UnitTests ;;
    functional) run_phpunit FunctionalTests ;;
    composer)   composer validate --strict --no-check-lock ;;
    audit)      composer audit --abandoned=report ;;
    ci)
        composer validate --strict --no-check-lock
        run_lint
        vendor/bin/php-cs-fixer fix --dry-run --diff
        vendor/bin/phpstan analyse --no-progress --memory-limit=512M
        run_phpunit UnitTests
        ;;
    *)
        echo "Unknown suite: ${SUITE}" >&2
        usage
        exit 64
        ;;
esac
