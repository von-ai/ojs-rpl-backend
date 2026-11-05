<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Jobs\ScanFile;
use App\Models\FileEntry;
use App\Services\Security\MockMalwareScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DefenderScannerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('azure');
    }

    public function test_mock_scanner_marks_clean_content_and_quarantines_eicar_virus_signature(): void
    {
        config([
            'filesecurity.driver' => 'mock',
            'filesecurity.disk' => 'azure',
        ]);

        $eicar = MockMalwareScanner::EICAR_SIGNATURE;

        foreach (['clean' => 'Dokumen naskah ilmiah SIRIDA yang aman dan bersih.', 'infected' => $eicar] as $expected => $content) {
            $path = 'scan-integration/'.Str::ulid().'.txt';
            Storage::disk('azure')->put($path, $content);

            $file = FileEntry::create([
                'storage_disk' => 'azure',
                'storage_path' => $path,
                'original_name' => 'uji.txt',
                'mime_type' => 'text/plain',
                'byte_size' => strlen($content),
                'checksum_sha256' => hash('sha256', $content),
                'virus_scan_status' => 'pending',
            ]);

            (new ScanFile($file->id))->handle(app(MockMalwareScanner::class));

            $this->assertSame($expected, $file->fresh()->virus_scan_status);
        }
    }

    public function test_event_grid_webhook_validates_subscription_handshake(): void
    {
        $validationCode = '512d38b6-d7b8-40f8-8752-823c33bdecab';

        $response = $this->postJson('/api/webhooks/azure-defender', [
            [
                'id' => 'test-handshake-id',
                'eventType' => 'Microsoft.EventGrid.SubscriptionValidationEvent',
                'data' => [
                    'validationCode' => $validationCode,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'validationResponse' => $validationCode,
            ]);
    }

    public function test_event_grid_webhook_updates_file_to_clean(): void
    {
        $path = 'manuscripts/uuid-1/clean-manuscript.pdf';
        Storage::disk('azure')->put($path, '%PDF-1.4 sample manuscript');

        $file = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => $path,
            'original_name' => 'clean-manuscript.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 25,
            'checksum_sha256' => hash('sha256', '%PDF-1.4 sample manuscript'),
            'virus_scan_status' => 'pending',
        ]);

        $response = $this->postJson('/api/webhooks/azure-defender', [
            [
                'id' => 'scan-event-1',
                'eventType' => 'Microsoft.Security.MalwareScanningResult',
                'data' => [
                    'blobUri' => 'https://devstoreaccount1.blob.core.windows.net/sirida-manuscripts/'.$path,
                    'scanResultType' => 'No threats found',
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('clean', $file->fresh()->virus_scan_status);
    }

    public function test_event_grid_webhook_quarantines_malicious_file(): void
    {
        $path = 'manuscripts/uuid-2/infected-manuscript.pdf';
        Storage::disk('azure')->put($path, 'eicar virus content');

        $file = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => $path,
            'original_name' => 'infected-manuscript.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 19,
            'checksum_sha256' => hash('sha256', 'eicar virus content'),
            'virus_scan_status' => 'pending',
        ]);

        $response = $this->postJson('/api/webhooks/azure-defender', [
            [
                'id' => 'scan-event-2',
                'eventType' => 'Microsoft.Security.MalwareScanningResult',
                'data' => [
                    'blobUri' => 'https://devstoreaccount1.blob.core.windows.net/sirida-manuscripts/'.$path,
                    'scanResultType' => 'Malicious',
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('infected', $file->fresh()->virus_scan_status);
    }
}
