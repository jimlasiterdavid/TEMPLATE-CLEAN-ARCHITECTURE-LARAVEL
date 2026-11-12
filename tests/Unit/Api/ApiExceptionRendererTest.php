<?php

namespace Tests\Unit\Api;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\Errors\NotFoundError;
use App\Infrastructure\Database\StoredProcedure\StoredProcedureException;
use App\Api\Exceptions\ApiExceptionRenderer;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionRendererTest extends TestCase
{
    public function test_erreur_metier_en_422_avec_son_message(): void
    {
        $response = ApiExceptionRenderer::toResponse(new BusinessRuleViolation('Solde insuffisant'));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(['success' => false, 'message' => 'Solde insuffisant'], $response->getData(true));
    }

    public function test_ressource_introuvable_en_404(): void
    {
        $response = ApiExceptionRenderer::toResponse(NotFoundError::for('Exemple', 9));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Exemple [9] introuvable.', $response->getData(true)['message']);
    }

    public function test_erreur_technique_masquee_hors_debug(): void
    {
        config(['app.debug' => false]);

        $response = ApiExceptionRenderer::toResponse(new StoredProcedureException('ps_x', '42000', 1305, 'PROCEDURE ps_x does not exist'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Erreur lors du traitement de la requête.', $response->getData(true)['message']);
    }

    public function test_erreur_technique_detaillee_en_debug(): void
    {
        config(['app.debug' => true]);

        $response = ApiExceptionRenderer::toResponse(new RuntimeException('boom'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('boom', $response->getData(true)['message']);
    }
}
