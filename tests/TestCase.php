<?php

namespace Tests;

use App\Models\User;
use App\Services\Auth\SessionRegistry;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesWorkflowFixtures;

abstract class TestCase extends BaseTestCase
{
    use CreatesWorkflowFixtures;

    public function createApplication()
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';
        // Opt-in MySQL tests always use a dedicated schema; normal test runs stay in memory.
        $database = getenv('RPL_TEST_MYSQL') === '1'
            ? ['DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => getenv('RPL_TEST_DB_PORT') ?: '13319', 'DB_DATABASE' => 'sirida_latest_test', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => getenv('RPL_TEST_DB_PASSWORD') ?: 'sirida_test_only', 'DB_URL' => '']
            : ['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => ''];
        foreach ($database as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }

    public function actingAs(Authenticatable $user, $guard = null)
    {
        parent::actingAs($user, $guard);
        if ($user instanceof User) {
            $this->withSession(app(SessionRegistry::class)->start($user));
        }

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('azure');
        Storage::fake('azure-backups');
    }
}
