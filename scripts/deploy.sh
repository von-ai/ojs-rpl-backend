#!/usr/bin/env bash
# =============================================================================
# SIRIDA - Deployment Script via Terraform (Azure & Cloudflare)
# Proyek: SIRIDA - BRIDA Kota Makassar
# =============================================================================

set -e

# Warna output terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
TERRAFORM_DIR="${PROJECT_ROOT}/terraform"

echo -e "${CYAN}===================================================================${NC}"
echo -e "${CYAN}             SIRIDA - Cloud Deployment via Terraform               ${NC}"
echo -e "${CYAN}===================================================================${NC}"

# 1. Cek ketersediaan Terraform / OpenTofu
TF_BIN=""
if command -v terraform &> /dev/null; then
    TF_BIN="terraform"
elif command -v tofu &> /dev/null; then
    TF_BIN="tofu"
else
    echo -e "${RED}[ERROR] Binary 'terraform' atau 'tofu' tidak ditemukan di PATH!${NC}"
    echo -e "Silakan instal Terraform (https://developer.hashicorp.com/terraform/downloads) terlebih dahulu."
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Menggunakan engine IaC: ${BLUE}${TF_BIN}${NC} ($(${TF_BIN} -version | head -n 1))"

# 2. Cek ketersediaan Azure CLI
if ! command -v az &> /dev/null; then
    echo -e "${YELLOW}[WARNING] Azure CLI ('az') tidak terdeteksi di sistem.${NC}"
    echo -e "Pastikan kredensial Azure (ARM_SUBSCRIPTION_ID, ARM_CLIENT_ID, ARM_CLIENT_SECRET, ARM_TENANT_ID) tersedia di environment."
else
    echo -e "${GREEN}[OK]${NC} Azure CLI terpasang ($(az version --output json 2>/dev/null | grep -o '"azure-cli": "[^"]*' || echo "detected"))"
fi

# 3. Cek file konfigurasi terraform.tfvars
cd "${TERRAFORM_DIR}"
if [ ! -f "terraform.tfvars" ]; then
    if [ -f "terraform.tfvars.example" ]; then
        echo -e "${YELLOW}[NOTICE] 'terraform.tfvars' tidak ditemukan.${NC}"
        echo -e "Menyalin dari 'terraform.tfvars.example'..."
        cp terraform.tfvars.example terraform.tfvars
        echo -e "${RED}[ACTION REQUIRED] Harap lengkapi nilai sensitif (app_key, db_password, dll.) di ${TERRAFORM_DIR}/terraform.tfvars sebelum apply!${NC}"
        exit 1
    else
        echo -e "${RED}[ERROR] File konfigurasi terraform.tfvars tidak ditemukan!${NC}"
        exit 1
    fi
fi

# Parameter flags
AUTO_APPROVE=""
RUN_MIGRATION=false
PLAN_ONLY=false

for arg in "$@"; do
    case $arg in
        --auto-approve|-y)
            AUTO_APPROVE="-auto-approve"
            shift
            ;;
        --migrate)
            RUN_MIGRATION=true
            shift
            ;;
        --plan-only)
            PLAN_ONLY=true
            shift
            ;;
        *)
            ;;
    esac
done

# 4. Inisialisasi Terraform
echo -e "\n${BLUE}--> [Step 1/3] Menjalankan ${TF_BIN} init -upgrade...${NC}"
${TF_BIN} init -upgrade

# 5. Validasi & Format Check
echo -e "\n${BLUE}--> [Step 2/3] Validasi sintaks modul Terraform...${NC}"
${TF_BIN} validate

# 6. Plan atau Apply
if [ "$PLAN_ONLY" = true ]; then
    echo -e "\n${BLUE}--> [Step 3/3] Menghasilkan execution plan (--plan-only)...${NC}"
    ${TF_BIN} plan
    echo -e "\n${GREEN}[SUCCESS] Execution plan selesai dihasilkan.${NC}"
    exit 0
fi

echo -e "\n${BLUE}--> [Step 3/3] Menerapkan konfigurasi infrastruktur (${TF_BIN} apply)...${NC}"
APPLY_LOG=$(mktemp)
set +e
${TF_BIN} apply ${AUTO_APPROVE} 2>&1 | tee "${APPLY_LOG}"
APPLY_EXIT_CODE=${PIPESTATUS[0]}
set -e

# Evaluasi kegagalan apply dan smart fallback otomatis jika SKU kehabisan kapasitas di region target
if [ ${APPLY_EXIT_CODE} -ne 0 ]; then
    if grep -q "CacheCapacityUnavailable.*Balanced_B0" "${APPLY_LOG}" || grep -q "Balanced_B0 is not currently available" "${APPLY_LOG}"; then
        echo -e "\n${YELLOW}===================================================================${NC}"
        echo -e "${YELLOW}[NOTICE] Kapasitas 'Balanced_B0' sedang penuh di region ini.${NC}"
        echo -e "${YELLOW}[FALLBACK] Mengaktifkan Smart Fallback otomatis ke SKU 'Balanced_B1'...${NC}"
        echo -e "${YELLOW}===================================================================${NC}\n"
        rm -f "${APPLY_LOG}"
        ${TF_BIN} apply ${AUTO_APPROVE} -var="redis_sku_name=Balanced_B1"
    else
        echo -e "\n${RED}[ERROR] Deployment Terraform gagal! Periksa rincian log di atas.${NC}"
        rm -f "${APPLY_LOG}"
        exit ${APPLY_EXIT_CODE}
    fi
fi
rm -f "${APPLY_LOG}"

echo -e "\n${GREEN}===================================================================${NC}"
echo -e "${GREEN}             DEPLOYMENT INFRASTRUKTUR BERHASIL!                    ${NC}"
echo -e "${GREEN}===================================================================${NC}"

# Mengambil output penting
WEB_URL=$(${TF_BIN} output -raw public_app_url 2>/dev/null || echo "N/A")
RG_NAME=$(${TF_BIN} output -raw resource_group_name 2>/dev/null || echo "N/A")
WEB_NAME=$(${TF_BIN} output -raw web_app_name 2>/dev/null || echo "N/A")
BLOB_EP=$(${TF_BIN} output -raw storage_primary_blob_endpoint 2>/dev/null || echo "N/A")

echo -e "Web App URL      : ${CYAN}${WEB_URL}${NC}"
echo -e "Resource Group   : ${CYAN}${RG_NAME}${NC}"
echo -e "Container App    : ${CYAN}${WEB_NAME}${NC}"
echo -e "Storage Endpoint : ${CYAN}${BLOB_EP}${NC}"

# Opsional: Menjalankan migrasi database
if [ "$RUN_MIGRATION" = true ]; then
    echo -e "\n${BLUE}--> Menjalankan migrasi database di Web Container...${NC}"
    if [ -f "${SCRIPT_DIR}/run-migration.sh" ]; then
        bash "${SCRIPT_DIR}/run-migration.sh"
    else
        echo -e "${YELLOW}File run-migration.sh tidak ditemukan.${NC}"
    fi
fi

echo -e "\n${YELLOW}Petunjuk Operasional:${NC}"
echo -e "1. Stream logs Web Container:"
echo -e "   ${CYAN}az containerapp logs show --name ${WEB_NAME} --resource-group ${RG_NAME} --type console --follow${NC}"
echo -e "2. Buka terminal interaktif:"
echo -e "   ${CYAN}az containerapp exec --name ${WEB_NAME} --resource-group ${RG_NAME} --container web${NC}"
echo -e "3. Hancurkan infrastruktur setelah pengujian (Ephemeral Teardown):"
echo -e "   ${CYAN}${SCRIPT_DIR}/destroy.sh${NC}"
