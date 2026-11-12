<?php

namespace Tests\Api;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * La documentation Swagger doit rester à jour avec les routes réellement exposées.
 */
class DocumentationTest extends TestCase
{
    private array $spec;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('l5-swagger:generate');
        $this->spec = json_decode(file_get_contents(storage_path('api-docs/api-docs.json')), true);
    }

    public function test_swagger_ui_est_servi(): void
    {
        $this->get('/documentation')
            ->assertOk()
            ->assertSee('swagger-ui', false);
    }

    public function test_spec_openapi_accessible(): void
    {
        $this->getJson('/docs')
            ->assertOk()
            ->assertJsonPath('info.title', 'TemplateApp API');
    }

    public function test_chaque_route_api_est_documentee(): void
    {
        $manquantes = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1/')) {
                continue;
            }

            $path = '/'.substr($route->uri(), \strlen('api/v1/'));

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                if (! isset($this->spec['paths'][$path][strtolower($method)])) {
                    $manquantes[] = "{$method} {$path}";
                }
            }
        }

        $this->assertSame([], $manquantes, 'Routes sans documentation #[OA\...]');
    }
}
