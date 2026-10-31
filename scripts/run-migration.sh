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

if [ -z "$RG_NAME" ] || [ -z "$WEB_NAME" ]; then
    echo -e "${RED}[ERROR] Gagal mendapatkan nama Container App atau Resource Group dari output Terraform.${NC}"
    echo -e "Pastikan infrastruktur telah di-apply terlebih dahulu."
    exit 1
fi

echo -e "${BLUE}--> Menjalankan 'php artisan migrate --force' di Container '${WEB_NAME}'...${NC}"
az containerapp exec \
    --name "${WEB_NAME}" \
    --resource-group "${RG_NAME}" \
    --container "web" \
    --command "php artisan migrate --force"

SEED=false
for arg in "$@"; do
    if [ "$arg" == "--seed" ]; then
        SEED=true
    fi
done

if [ "$SEED" = true ]; then
    echo -e "${BLUE}--> Menjalankan 'php artisan db:seed --force' di Container '${WEB_NAME}'...${NC}"
    az containerapp exec \
        --name "${WEB_NAME}" \
        --resource-group "${RG_NAME}" \
        --container "web" \
        --command "php artisan db:seed --force"
fi

echo -e "\n${GREEN}[SUCCESS] Migrasi database berhasil dieksekusi di Azure Container Apps!${NC}"
