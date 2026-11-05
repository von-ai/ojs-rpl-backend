#!/usr/bin/env bash
# =============================================================================
# SIRIDA - Teardown / Destruction Script via Terraform
# Pola Ephemeral Infrastructure: Hancurkan resource untuk menghentikan tagihan
# Mendukung Pemisahan Penghancuran Stateless App vs Stateful Blob Storage
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
SCOPE="" # app-only | storage-only | all

while [[ $# -gt 0 ]]; do
    case $1 in
        --auto-approve|-y)
            AUTO_APPROVE="-auto-approve"
            shift
            ;;
        --app-only|--stateless-only|--exclude-storage)
            SCOPE="app-only"
            shift
            ;;
        --storage-only)
            SCOPE="storage-only"
            shift
            ;;
        --all|--destroy-all|--full)
            SCOPE="all"
            shift
            ;;
        --help|-h)
            echo -e "Penggunaan: $0 [OPSI]"
            echo -e "Opsi:"
            echo -e "  --app-only          Hanya hancurkan Stateless App (ACA, Redis, Cloudflare, Monitor)."
            echo -e "                      Azure Blob Storage & file naskah TETAP AMAN (mencegah dangling metadata di TiDB)."
            echo -e "  --storage-only      Hanya hancurkan Azure Blob Storage (Stateful RG, Account, Containers)."
            echo -e "  --all               Hancurkan SELURUH infrastruktur (Stateless + Stateful)."
            echo -e "  --auto-approve, -y  Menyetujui eksekusi penghancuran secara otomatis tanpa konfirmasi."
            exit 0
            ;;
        *)
            shift
            ;;
    esac
done

# Jika flag scope tidak ditentukan, tampilkan menu interaktif
if [ -z "$SCOPE" ]; then
    echo -e "\nPilih cakupan infrastruktur yang ingin Anda hancurkan:"
    echo -e "  ${GREEN}1) [REKOMENDASI] Stateless App Saja${NC}"
    echo -e "     - Menghapus: Container Apps (Web, Worker, Job), Redis, Log Analytics, Cloudflare."
    echo -e "     - ${CYAN}Azure Blob Storage TETAP UTUH${NC} (seluruh naskah tersimpan aman & metadata TiDB tidak rusak)."
    echo -e "  ${YELLOW}2) Azure Blob Storage Saja${NC}"
    echo -e "     - Menghapus: Storage Account, Containers, dan seluruh file naskah di dalamnya."
    echo -e "     - Stateless App tetap berjalan (namun unduhan file naskah akan 404)."
    echo -e "  ${RED}3) SEMUA Komponen (Full Teardown)${NC}"
    echo -e "     - Menghapus: Seluruh resource cloud (App + Storage) tanpa terkecuali."
    echo -e "  4) Batal"
    echo ""
    read -p "Masukkan pilihan [1-4] (default: 1): " choice
    choice=${choice:-1}

    case $choice in
        1)
            SCOPE="app-only"
            ;;
        2)
            SCOPE="storage-only"
            ;;
        3)
            SCOPE="all"
            ;;
        *)
            echo -e "${BLUE}Operasi dibatalkan.${NC}"
            exit 0
            ;;
    esac
fi

# Target array deklaratif
APP_TARGETS=(
    "-target=cloudflare_workers_domain.custom_domain"
    "-target=cloudflare_workers_script.proxy"
    "-target=cloudflare_record.cname_web"
    "-target=azurerm_application_insights_standard_web_test.availability"
    "-target=azurerm_monitor_metric_alert.sla_alert"
    "-target=azurerm_monitor_action_group.action_group"
    "-target=azurerm_container_app.scheduler"
    "-target=azurerm_container_app.worker"
    "-target=azurerm_container_app.web"
    "-target=azurerm_managed_redis.redis"
    "-target=azurerm_container_app_environment.env"
    "-target=azurerm_application_insights.appinsights"
    "-target=azurerm_log_analytics_workspace.logs"
    "-target=random_string.suffix"
    "-target=azurerm_resource_group.rg"
)

STORAGE_TARGETS=(
    "-target=azurerm_management_lock.storage_lock"
    "-target=azurerm_security_center_storage_defender.defender"
    "-target=azurerm_storage_management_policy.lifecycle"
    "-target=azurerm_storage_container.backups"
    "-target=azurerm_storage_container.manuscripts"
    "-target=azurerm_storage_account.storage"
    "-target=random_string.storage_suffix"
    "-target=azurerm_resource_group.storage_rg"
)

# Konfirmasi interaktif jika bukan auto-approve
if [ -z "$AUTO_APPROVE" ]; then
    echo ""
    if [ "$SCOPE" == "app-only" ]; then
        echo -e "${YELLOW}[KONFIRMASI] Anda akan menghancurkan Stateless App:${NC}"
        echo -e "  - Azure Resource Group (App), ACA Web, Worker, Scheduled Job"
        echo -e "  - Azure Cache for Redis & Log Analytics Workspace"
        echo -e "  - Cloudflare Worker & DNS"
        echo -e "  ${GREEN}✓ Azure Blob Storage & berkas naskah akan TETAP AMAN dan utuh.${NC}"
        echo ""
        read -p "Ketik 'destroy' untuk melanjutkan: " confirm
        if [ "$confirm" != "destroy" ]; then
            echo -e "${BLUE}Operasi dibatalkan.${NC}"
            exit 0
        fi
    elif [ "$SCOPE" == "storage-only" ]; then
        echo -e "${RED}[PERINGATAN BAHAYA] Anda akan menghapus Azure Blob Storage:${NC}"
        echo -e "  - Storage Account dan SELURUH berkas manuskrip/bukti pembayaran di dalamnya."
        echo -e "  - Tindakan ini akan menyebabkan metadata di TiDB kehilangan berkas fisiknya (dangling)!"
        echo ""
        read -p "Ketik 'destroy-storage' untuk melanjutkan: " confirm
        if [ "$confirm" != "destroy-storage" ]; then
            echo -e "${BLUE}Operasi dibatalkan.${NC}"
            exit 0
        fi
    elif [ "$SCOPE" == "all" ]; then
        echo -e "${RED}[PERINGATAN BAHAYA] Anda akan menghapus SELURUH infrastruktur cloud SIRIDA:${NC}"
        echo -e "  - Stateless App (ACA, Redis, Logs, Cloudflare)"
        echo -e "  - Stateful Storage (Storage Account, seluruh naskah manuskrip)"
        echo ""
        read -p "Ketik 'destroy-all' untuk melanjutkan: " confirm
        if [ "$confirm" != "destroy-all" ]; then
            echo -e "${BLUE}Operasi dibatalkan.${NC}"
            exit 0
        fi
    fi
fi

# Eksekusi penghancuran sesuai scope
if [ "$SCOPE" == "app-only" ]; then
    echo -e "\n${BLUE}--> Menjalankan ${TF_BIN} destroy untuk Stateless App...${NC}"
    ${TF_BIN} destroy ${AUTO_APPROVE} "${APP_TARGETS[@]}"
    echo -e "\n${GREEN}===================================================================${NC}"
    echo -e "${GREEN}    STATELESS APP BERHASIL DIHAPUS (BIAYA KOMPUTASI TELAH BERHENTI) ${NC}"
    echo -e "${GREEN}    ✓ Azure Blob Storage & berkas naskah tetap tersimpan aman.     ${NC}"
    echo -e "${GREEN}===================================================================${NC}"

elif [ "$SCOPE" == "storage-only" ]; then
    echo -e "\n${BLUE}--> Menjalankan ${TF_BIN} destroy untuk Azure Blob Storage...${NC}"
    ${TF_BIN} destroy ${AUTO_APPROVE} "${STORAGE_TARGETS[@]}"
    echo -e "\n${GREEN}===================================================================${NC}"
    echo -e "${GREEN}         AZURE BLOB STORAGE BERHASIL DIHANCURKAN                   ${NC}"
    echo -e "${GREEN}===================================================================${NC}"

elif [ "$SCOPE" == "all" ]; then
    echo -e "\n${BLUE}--> Menjalankan ${TF_BIN} destroy untuk SEMUA resource...${NC}"
    # Lepas lock storage terlebih dahulu jika ada agar destroy tidak terblokir
    ${TF_BIN} destroy ${AUTO_APPROVE} -target=azurerm_management_lock.storage_lock 2>/dev/null || true
    ${TF_BIN} destroy ${AUTO_APPROVE}
    echo -e "\n${GREEN}===================================================================${NC}"
    echo -e "${GREEN}    SELURUH RESOURCE CLOUD BERHASIL DIHAPUS (BIAYA TELAH DIHENTIKAN) ${NC}"
    echo -e "${GREEN}===================================================================${NC}"
fi
