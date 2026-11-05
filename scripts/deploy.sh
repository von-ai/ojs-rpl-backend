#!/usr/bin/env bash
# =============================================================================
# SIRIDA - Deployment Script via Terraform (Azure & Cloudflare)
# Proyek: SIRIDA - BRIDA Kota Makassar
# Mendukung Pemisahan Siklus Hidup Stateful (Blob Storage) vs Stateless (App)
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
SCOPE="all" # all | storage-only | app-only

while [[ $# -gt 0 ]]; do
    case $1 in
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
        --storage-only)
            SCOPE="storage-only"
            shift
            ;;
        --app-only)
            SCOPE="app-only"
            shift
            ;;
        --help|-h)
            echo -e "Penggunaan: $0 [OPSI]"
            echo -e "Opsi:"
            echo -e "  --storage-only      Hanya inisiasi/deploy Azure Blob Storage & Defender (Stateful)"
            echo -e "  --app-only          Hanya inisiasi/deploy Stateless App (ACA, Redis, Cloudflare, Monitor)"
            echo -e "  --migrate           Jalankan migrasi database otomatis setelah deploy"
            echo -e "  --plan-only         Hanya lakukan kalkulasi rencana perubahan tanpa menerapkan"
            echo -e "  --auto-approve, -y  Menyetujui eksekusi perubahan secara otomatis"
            exit 0
            ;;
        *)
            shift
            ;;
    esac
done

TARGET_ARGS=()
if [ "$SCOPE" == "storage-only" ]; then
    echo -e "\n${YELLOW}[MODE] Inisiasi Khusus Azure Blob Storage (Stateful Infrastructure)${NC}"
    TARGET_ARGS=(
        "-target=azurerm_resource_group.storage_rg"
        "-target=random_string.storage_suffix"
        "-target=azurerm_storage_account.storage"
        "-target=azurerm_storage_container.manuscripts"
        "-target=azurerm_storage_container.backups"
        "-target=azurerm_storage_management_policy.lifecycle"
        "-target=azurerm_security_center_storage_defender.defender"
        "-target=azurerm_management_lock.storage_lock"
    )
elif [ "$SCOPE" == "app-only" ]; then
    echo -e "\n${YELLOW}[MODE] Inisiasi Khusus Stateless Application (ACA, Redis, Cloudflare)${NC}"
    TARGET_ARGS=(
        "-target=azurerm_resource_group.rg"
        "-target=random_string.suffix"
        "-target=azurerm_log_analytics_workspace.logs"
        "-target=azurerm_application_insights.appinsights"
        "-target=azurerm_container_app_environment.env"
        "-target=azurerm_managed_redis.redis"
        "-target=azurerm_container_app.web"
        "-target=azurerm_container_app.worker"
        "-target=azurerm_container_app.scheduler"
        "-target=azurerm_monitor_action_group.action_group"
        "-target=azurerm_monitor_metric_alert.sla_alert"
        "-target=azurerm_application_insights_standard_web_test.availability"
        "-target=cloudflare_record.cname_web"
        "-target=cloudflare_workers_script.proxy"
        "-target=cloudflare_workers_domain.custom_domain"
    )
fi

# 4. Inisialisasi Terraform
echo -e "\n${BLUE}--> [Step 1/3] Menjalankan ${TF_BIN} init -upgrade...${NC}"
${TF_BIN} init -upgrade

# 5. Validasi & Format Check
echo -e "\n${BLUE}--> [Step 2/3] Validasi sintaks modul Terraform...${NC}"
${TF_BIN} validate

# 6. Plan atau Apply
if [ "$PLAN_ONLY" = true ]; then
    echo -e "\n${BLUE}--> [Step 3/3] Menghasilkan execution plan (--plan-only)...${NC}"
    ${TF_BIN} plan "${TARGET_ARGS[@]}"
    echo -e "\n${GREEN}[SUCCESS] Execution plan selesai dihasilkan.${NC}"
    exit 0
fi

echo -e "\n${BLUE}--> [Step 3/3] Menerapkan konfigurasi infrastruktur (${TF_BIN} apply)...${NC}"
${TF_BIN} apply ${AUTO_APPROVE} "${TARGET_ARGS[@]}"

echo -e "\n${GREEN}===================================================================${NC}"
echo -e "${GREEN}             DEPLOYMENT INFRASTRUKTUR BERHASIL!                    ${NC}"
echo -e "${GREEN}===================================================================${NC}"

# Mengambil output penting
WEB_URL=$(${TF_BIN} output -raw public_app_url 2>/dev/null || echo "N/A")
RG_NAME=$(${TF_BIN} output -raw resource_group_name 2>/dev/null || echo "N/A")
STORAGE_RG=$(${TF_BIN} output -raw storage_resource_group_name 2>/dev/null || echo "N/A")
WEB_NAME=$(${TF_BIN} output -raw web_app_name 2>/dev/null || echo "N/A")
BLOB_EP=$(${TF_BIN} output -raw storage_primary_blob_endpoint 2>/dev/null || echo "N/A")

echo -e "Web App URL        : ${CYAN}${WEB_URL}${NC}"
echo -e "App Resource Group : ${CYAN}${RG_NAME}${NC}"
echo -e "Storage RG         : ${CYAN}${STORAGE_RG}${NC}"
echo -e "Container App      : ${CYAN}${WEB_NAME}${NC}"
echo -e "Storage Endpoint   : ${CYAN}${BLOB_EP}${NC}"

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
echo -e "3. Hancurkan hanya Stateless App (Blob Storage aman):"
echo -e "   ${CYAN}${SCRIPT_DIR}/destroy.sh --app-only${NC}"
echo -e "4. Hancurkan hanya Storage Account:"
echo -e "   ${CYAN}${SCRIPT_DIR}/destroy.sh --storage-only${NC}"
