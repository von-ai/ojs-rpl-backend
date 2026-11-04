<?php

namespace Tests\Feature\Security;

use App\Jobs\ScanFile;
use App\Models\FileEntry;
use App\Services\ClamAvScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClamAvIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_scanner_distinguishes_clean_content_and_eicar(): void
    {
        if (! getenv('RPL_TEST_CLAMD_HOST')) {
            $this->markTestSkipped('Set RPL_TEST_CLAMD_HOST to run the isolated ClamAV integration test.');
        }
        config(['filesecurity.driver' => 'clamd', 'filesecurity.clamd_host' => getenv('RPL_TEST_CLAMD_HOST'), 'filesecurity.clamd_port' => (int) (getenv('RPL_TEST_CLAMD_PORT') ?: 13320)]);
        Storage::fake('local');
        $eicar = 'X5O!P%@AP[4\\PZX54(P^)7CC)7}'.'$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        foreach (['clean' => 'Dokumen pengujian SIRIDA yang bersih.', 'infected' => $eicar] as $expected => $content) {
            $path = 'scan-integration/'.Str::ulid().'.txt';
            Storage::disk('local')->put($path, $content);
            $file = FileEntry::create(['storage_disk' => 'local', 'storage_path' => $path, 'original_name' => 'uji.txt', 'mime_type' => 'text/plain', 'byte_size' => strlen($content), 'checksum_sha256' => hash('sha256', $content), 'virus_scan_status' => 'pending']);
            (new ScanFile($file->id))->handle(app(ClamAvScanner::class));
            $this->assertSame($expected, $file->fresh()->virus_scan_status);
        }
    }
}
