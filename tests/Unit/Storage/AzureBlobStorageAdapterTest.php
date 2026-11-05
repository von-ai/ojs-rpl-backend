<?php

declare(strict_types=1);

namespace Tests\Unit\Storage;

use App\Services\Storage\AzureBlobStorageAdapter;
use App\Services\Storage\AzureBlobStorageClient;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AzureBlobStorageAdapterTest extends TestCase
{
    protected AzureBlobStorageClient $client;

    protected AzureBlobStorageAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::forgetDisk('azure');

        $this->client = new AzureBlobStorageClient(
            accountName: 'devstoreaccount1',
            accountKey: 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
            container: 'sirida-manuscripts',
            baseUrl: 'http://127.0.0.1:10000/devstoreaccount1'
        );

        $this->adapter = new AzureBlobStorageAdapter($this->client);
    }

    public function test_adapter_generates_correct_blob_urls(): void
    {
        $url = $this->adapter->getUrl('submissions/01m639/manuscript.pdf');

        $this->assertEquals(
            'http://127.0.0.1:10000/devstoreaccount1/sirida-manuscripts/submissions/01m639/manuscript.pdf',
            $url
        );
    }

    public function test_adapter_generates_valid_sas_download_url(): void
    {
        $expiry = Carbon::now('UTC')->addMinutes(15);
        $sasUrl = $this->adapter->getTemporaryUrl('submissions/01m639/manuscript.pdf', $expiry);

        $this->assertStringStartsWith(
            'http://127.0.0.1:10000/devstoreaccount1/sirida-manuscripts/submissions/01m639/manuscript.pdf?',
            $sasUrl
        );
        $this->assertStringContainsString('sp=r', $sasUrl);
        $this->assertStringContainsString('sr=b', $sasUrl);
        $this->assertStringContainsString('sig=', $sasUrl);
    }

    public function test_adapter_generates_valid_sas_upload_url(): void
    {
        $expiry = Carbon::now('UTC')->addMinutes(15);
        $result = $this->adapter->temporaryUploadUrl('submissions/01m639/manuscript.pdf', $expiry);

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertStringContainsString('sp=c&', $result['url']);
        $this->assertEquals('BlockBlob', $result['headers']['x-ms-blob-type']);
    }

    public function test_storage_disk_azure_is_properly_bound_in_laravel(): void
    {
        $disk = Storage::disk('azure');

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertStringContainsString('sirida-manuscripts', $disk->url('test.pdf'));
    }

    public function test_shared_key_requests_work_against_path_style_azurite(): void
    {
        if (! getenv('RPL_TEST_AZURITE_URL')) {
            $this->markTestSkipped('Set RPL_TEST_AZURITE_URL to run the isolated Azurite test.');
        }

        $client = new AzureBlobStorageClient(
            accountName: 'siridanotion',
            accountKey: base64_encode('sirida-test-only-azurite-key'),
            container: 'sirida-manuscripts',
            baseUrl: getenv('RPL_TEST_AZURITE_URL'),
            prefix: 'test',
        );
        $path = 'database/test file.sql.gz';

        $this->assertTrue($client->putBlob($path, 'test backup'));
        try {
            $this->assertTrue($client->blobExists($path));
            $this->assertSame('test backup', $client->getBlob($path));
            $listed = iterator_to_array($client->listBlobs('database'));
            $this->assertContains($path, array_column($listed, 'path'));
        } finally {
            $client->deleteBlob($path);
        }
    }
}
