#!/usr/bin/env bash
# =============================================================================
# SIRIDA - Eksekusi Migrasi Database & Seeder di Azure Container Apps
# =============================================================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
TERRAFORM_DIR="${PROJECT_ROOT}/terraform"

cd "${TERRAFORM_DIR}"

TF_BIN=""
if command -v terraform &> /dev/null; then
    TF_BIN="terraform"
elif command -v tofu &> /dev/null; then
    TF_BIN="tofu"
else
    echo -e "${RED}[ERROR] Binary 'terraform' atau 'tofu' tidak ditemukan di PATH!${NC}"
    exit 1
fi

RG_NAME=$(${TF_BIN} output -raw resource_group_name 2>/dev/null || echo "")
WEB_NAME=$(${TF_BIN} output -raw web_app_name 2>/dev/null || echo "")

WEB_URL=$(${TF_BIN} output -raw public_app_url 2>/dev/null || echo "")

if [ -z "$RG_NAME" ] || [ -z "$WEB_NAME" ]; then
    echo -e "${RED}[ERROR] Gagal mendapatkan nama Container App atau Resource Group dari output Terraform.${NC}"
    echo -e "Pastikan infrastruktur telah di-apply terlebih dahulu."
    exit 1
fi

# Bangunkan kontainer jika sedang scale-to-zero
if [ -n "$WEB_URL" ] && [ "$WEB_URL" != "N/A" ]; then
    echo -e "${BLUE}--> Memastikan Web Container aktif (wake-up ping: ${WEB_URL}/up)...${NC}"
    curl -s -k -m 15 -o /dev/null "${WEB_URL}/up" || true
    sleep 5
fi

FRESH=false
SEED=false

for arg in "$@"; do
    case $arg in
        --fresh)
            FRESH=true
            ;;
        --seed)
            SEED=true
            ;;
        *)
            ;;
    esac
done

CMD="php /var/www/html/artisan migrate"
if [ "$FRESH" = true ]; then
    CMD="php /var/www/html/artisan migrate:fresh"
fi

if [ "$SEED" = true ]; then
    CMD="${CMD} --seed"
fi

CMD="${CMD} --force"

echo -e "${BLUE}--> Menjalankan '${CMD}' di Container '${WEB_NAME}'...${NC}"
az containerapp exec \
    --name "${WEB_NAME}" \
    --resource-group "${RG_NAME}" \
    --container "web" \
    --command "${CMD}"

echo -e "\n${GREEN}[SUCCESS] Migrasi database berhasil dieksekusi di Azure Container Apps!${NC}"
