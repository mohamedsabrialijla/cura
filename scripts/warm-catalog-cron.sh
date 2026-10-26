#!/usr/bin/env bash
# تسخين كاش كتالوج Keepersport — للاستخدام من cron على السيرفر.
# مثال cron:
#   0 3 * * * /bin/bash /home/keepersport/public_html/project/scripts/warm-catalog-cron.sh

set -u

PROJECT_DIR="${KEEPER_PROJECT_DIR:-/home/keepersport/public_html/project}"
PHP_BIN="${KEEPER_PHP_BIN:-php}"
MEMORY_MB="${KEEPER_WARM_MEMORY_MB:-1024}"
LOG_FILE="${PROJECT_DIR}/storage/logs/catalog-warm.log"

mkdir -p "${PROJECT_DIR}/storage/logs"
touch "${LOG_FILE}"
chmod 664 "${LOG_FILE}" 2>/dev/null || true

{
  echo "========================================"
  echo "=== $(date '+%Y-%m-%d %H:%M:%S %Z') warm start ==="
  echo "PROJECT_DIR=${PROJECT_DIR}"
  echo "PHP_BIN=${PHP_BIN}"
  echo "PWD=$(pwd)"
  echo "USER=${USER:-unknown}"
} >> "${LOG_FILE}"

if [ ! -d "${PROJECT_DIR}" ]; then
  echo "ERROR: project directory not found: ${PROJECT_DIR}" >> "${LOG_FILE}"
  exit 1
fi

if [ ! -f "${PROJECT_DIR}/artisan" ]; then
  echo "ERROR: artisan not found in ${PROJECT_DIR}" >> "${LOG_FILE}"
  exit 1
fi

cd "${PROJECT_DIR}" || {
  echo "ERROR: cannot cd to ${PROJECT_DIR}" >> "${LOG_FILE}"
  exit 1
}

if ! command -v "${PHP_BIN}" >/dev/null 2>&1; then
  echo "ERROR: php binary not found: ${PHP_BIN}" >> "${LOG_FILE}"
  echo "Try: which php  (then set KEEPER_PHP_BIN in cron)" >> "${LOG_FILE}"
  exit 1
fi

"${PHP_BIN}" -d "memory_limit=${MEMORY_MB}M" artisan keepersport:warm-catalog-cache --products-limit="${KEEPER_WARM_PRODUCTS_LIMIT:-2000}" --memory="${MEMORY_MB}" >> "${LOG_FILE}" 2>&1
EXIT_CODE=$?

{
  echo "=== $(date '+%Y-%m-%d %H:%M:%S %Z') warm end (exit ${EXIT_CODE}) ==="
  echo ""
} >> "${LOG_FILE}"

exit "${EXIT_CODE}"
