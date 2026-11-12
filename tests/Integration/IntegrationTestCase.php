<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\DB;
use PDO;
use Tests\TestCase;
use Throwable;

/**
 * Base des tests d'intégration : vraie base PostgreSQL et vraies fonctions/procédures
 * (chargées depuis tests/Integration/fixtures/pgsql). Connexion via les variables
 * INTEGRATION_DB_* de phpunit.xml ; les tests sont ignorés si la base est injoignable.
 */
abstract class IntegrationTestCase extends TestCase
{
    /** Fichiers de fixtures à charger, relatifs à tests/Integration/fixtures/pgsql. */
    protected array $fixtures = ['exemple.sql'];

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'driver' => 'pgsql',
            'host' => env('INTEGRATION_DB_HOST', '127.0.0.1'),
            'port' => env('INTEGRATION_DB_PORT', '5433'),
            'database' => env('INTEGRATION_DB_DATABASE', 'template_app_test'),
            'username' => env('INTEGRATION_DB_USERNAME', 'template_app'),
            'password' => env('INTEGRATION_DB_PASSWORD', 'template_app'),
            'charset' => 'utf8',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ];

        try {
            new PDO("pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}", $config['username'], $config['password']);
        } catch (Throwable $e) {
            $this->markTestSkipped("Base d'intégration injoignable : {$e->getMessage()}");
        }

        config(['database.connections.integration' => $config, 'database.default' => 'integration']);
        DB::purge('integration');

        foreach ($this->fixtures as $fixture) {
            DB::unprepared(file_get_contents(__DIR__.'/fixtures/pgsql/'.$fixture));
        }
    }
}
