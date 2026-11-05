<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DefenderWebhookController extends Controller
{
    /**
     * Menangani webhook event dari Azure Event Grid untuk Microsoft Defender for Storage Malware Scanning.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        // Azure Event Grid dapat mengirimkan single event atau array of events
        $events = isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];

        foreach ($events as $event) {
            $eventType = $event['eventType'] ?? '';
            $data = $event['data'] ?? [];

            // 1. Handshake Validasi Langganan Event Grid
            if ($eventType === 'Microsoft.EventGrid.SubscriptionValidationEvent') {
                $validationCode = $data['validationCode'] ?? '';

                return response()->json([
                    'validationResponse' => $validationCode,
                ]);
            }

            // 2. Pemrosesan Hasil Scan Malware dari Microsoft Defender for Storage
            if ($eventType === 'Microsoft.Security.MalwareScanningResult') {
                $blobUri = (string) ($data['blobUri'] ?? '');
                $scanResult = (string) ($data['scanResultType'] ?? '');

                if ($blobUri !== '') {
                    $this->processScanResult($blobUri, $scanResult, $data);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function processScanResult(string $blobUri, string $scanResult, array $details): void
    {
        // Ekstrak relative path blob dari URI (misal: "https://account.blob.core.windows.net/sirida-manuscripts/manuscripts/uuid/file.pdf")
        $parsedUrl = parse_url($blobUri, PHP_URL_PATH);
        if (! $parsedUrl) {
            return;
        }

        $pathSegments = explode('/', trim($parsedUrl, '/'));
        // Elemen pertama adalah nama container, sisanya adalah storage_path
        if (count($pathSegments) < 2) {
            return;
        }

        array_shift($pathSegments); // Buang container
        $storagePath = implode('/', $pathSegments);

        $file = FileEntry::where('storage_path', $storagePath)->first();
        if (! $file) {
            // Coba pencarian dengan awalan URI jika path persis tidak cocok
            $file = FileEntry::where('storage_path', 'LIKE', '%'.basename($storagePath))->first();
        }

        if (! $file) {
            Log::info("DefenderWebhook: Berkas tidak ditemukan pada database untuk path '{$storagePath}'.");

            return;
        }

        $newStatus = strcasecmp($scanResult, 'No threats found') === 0
            ? 'clean'
            : (strcasecmp($scanResult, 'Malicious') === 0 ? 'infected' : 'error');

        $file->update(['virus_scan_status' => $newStatus]);

        if ($newStatus === 'infected') {
            Log::warning("DefenderWebhook: TERDETEKSI MALWARE! Berkas '{$file->original_name}' (ID: {$file->id}) dikarantina oleh Microsoft Defender.", [
                'details' => $details,
            ]);
        } else {
            Log::info("DefenderWebhook: Berkas '{$file->original_name}' (ID: {$file->id}) lulus pemindaian Microsoft Defender (Status: {$newStatus}).");
        }
    }
}
