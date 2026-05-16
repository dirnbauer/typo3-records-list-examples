#!/usr/bin/env bash

set -euo pipefail

SUITE="ci"

while getopts "s:p:" option; do
    case "${option}" in
        s)
            SUITE="${OPTARG}"
            ;;
        p)
            ;;
        *)
            echo "Usage: $0 [-s composerValidate|xlf|phpstan|ci] [-p php-version]" >&2
            exit 1
            ;;
    esac
done

case "${SUITE}" in
    composerValidate)
        composer validate --strict --no-check-publish
        ;;
    xlf)
        php Build/Scripts/validate-xlf.php
        ;;
    phpstan)
        vendor/bin/phpstan analyse --no-progress --memory-limit=512M
        ;;
    ci)
        composer validate --strict --no-check-publish
        php Build/Scripts/validate-xlf.php
        vendor/bin/phpstan analyse --no-progress --memory-limit=512M
        ;;
    *)
        echo "Unknown suite: ${SUITE}" >&2
        exit 1
        ;;
esac
