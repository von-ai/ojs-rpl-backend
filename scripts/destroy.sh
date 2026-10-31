#!/usr/bin/env bash
# =============================================================================
# SIRIDA - Teardown / Destruction Script via Terraform
# Pola Ephemeral Infrastructure: Hancurkan resource untuk menghentikan tagihan
# =============================================================================

set -e

RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
TERRAFORM_DIR="${PROJECT_ROOT}/terraform"

echo -e "${RED}===================================================================${NC}"
echo -e "${RED}         SIRIDA - PENGHANCURAN INFRASTRUKTUR CLOUD (TEARDOWN)       ${NC}"
echo -e "${RED}===================================================================${NC}"

# Cek binary terraform / tofu
TF_BIN=""
if command -v terraform &> /dev/null; then
    TF_BIN="terraform"
elif command -v tofu &> /dev/null; then
    TF_BIN="tofu"
else
    echo -e "${RED}[ERROR] Binary 'terraform' atau 'tofu' tidak ditemukan di PATH!${NC}"
    exit 1
fi

cd "${TERRAFORM_DIR}"

AUTO_APPROVE=""
for arg in "$@"; do
    case $arg in
        --auto-approve|-y)
            AUTO_APPROVE="-auto-approve"
            shift
            ;;
        *)
            ;;
    esac
done

if [ -z "$AUTO_APPROVE" ]; then
    echo -e "${YELLOW}[PERINGATAN] Aksi ini akan menghapus seluruh resource cloud SIRIDA:${NC}"
    echo -e "  - Azure Resource Group, ACA Web, Worker, Scheduled Job"
    echo -e "  - Azure Cache for Redis & Azure Blob Storage"
    echo -e "  - Log Analytics Workspace & Application Insights"
    echo -e "  - Cloudflare DNS Record & WAF Ruleset"
    echo ""
    read -p "Apakah Anda yakin ingin melanjutkan penghancuran? (ketik 'destroy' untuk konfirmasi): " confirm
    if [ "$confirm" != "destroy" ]; then
        echo -e "${BLUE}Operasi dibatalkan.${NC}"
        exit 0
    fi
fi

echo -e "\n${BLUE}--> Menjalankan ${TF_BIN} destroy...${NC}"
${TF_BIN} destroy ${AUTO_APPROVE}

echo -e "\n${GREEN}===================================================================${NC}"
echo -e "${GREEN}    SELURUH RESOURCE CLOUD BERHASIL DIHAPUS (BIAYA TELAH DIHENTIKAN) ${NC}"
echo -e "${GREEN}===================================================================${NC}"
