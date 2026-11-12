<?php

namespace Tests\Api\V1;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_au_format_api(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data' => ['app', 'version', 'database', 'time']]);
    }

    public function test_route_inconnue_en_json_404(): void
    {
        $this->getJson('/api/v1/inexistant')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Ressource introuvable.']);
    }

    public function test_cors_autorise_le_front_angular(): void
    {
        $this->withHeaders([
            'Origin' => 'http://localhost:4200',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/health')
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:4200');
    }
}
