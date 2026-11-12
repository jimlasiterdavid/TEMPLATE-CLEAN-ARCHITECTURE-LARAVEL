<?php

namespace Tests\Api\V1;

use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use Mockery;
use RuntimeException;
use Tests\Support\Fakes\InMemoryExempleRepository;
use Tests\TestCase;

/**
 * Tests du contrat de l'API :
 * codes HTTP, structure JSON, en-têtes, validation, erreurs.
 */
class ExempleApiTest extends TestCase
{
    private InMemoryExempleRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new InMemoryExempleRepository;
        $this->app->instance(ExempleRepository::class, $this->repo);
    }

    public function test_get_liste_contrat(): void
    {
        $this->repo->ajouter('Recharge', 1500);

        $this->getJson('/api/v1/exemples')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [['id', 'libelle', 'montant']],
            ]);
    }

    public function test_get_liste_pagination_invalide_422(): void
    {
        $this->getJson('/api/v1/exemples?par_page=500&page=0')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['par_page', 'page']);
    }

    public function test_get_detail_contrat(): void
    {
        $exemple = $this->repo->ajouter('Recharge', 1500);

        $this->getJson("/api/v1/exemples/{$exemple->id}")
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'OK',
                'data' => ['id' => 1, 'libelle' => 'Recharge', 'montant' => 1500],
            ]);
    }

    public function test_get_detail_introuvable_404(): void
    {
        $this->getJson('/api/v1/exemples/99')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Exemple [99] introuvable.']);
    }

    public function test_get_detail_id_non_numerique_404(): void
    {
        $this->getJson('/api/v1/exemples/abc')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_post_creation_201(): void
    {
        $this->postJson('/api/v1/exemples', ['libelle' => 'Paiement', 'montant' => 2500.5])
            ->assertCreated()
            ->assertExactJson([
                'success' => true,
                'message' => 'Créé avec succès.',
                'data' => ['id' => 1, 'libelle' => 'Paiement', 'montant' => 2500.5],
            ]);
    }

    public function test_post_donnees_invalides_422(): void
    {
        $this->postJson('/api/v1/exemples', ['libelle' => str_repeat('x', 300), 'montant' => 'abc'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Les données envoyées sont invalides.')
            ->assertJsonStructure(['errors' => ['libelle', 'montant']]);
    }

    public function test_post_corps_vide_422(): void
    {
        $this->postJson('/api/v1/exemples')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['libelle', 'montant']);
    }

    public function test_methode_non_autorisee_405(): void
    {
        $this->deleteJson('/api/v1/exemples/1')
            ->assertStatus(405)
            ->assertJsonPath('success', false);
    }

    public function test_erreur_metier_procedure_422(): void
    {
        $repo = Mockery::mock(ExempleRepository::class);
        $repo->shouldReceive('creer')->andThrow(new BusinessRuleViolation('Ce libellé existe déjà.'));
        $this->app->instance(ExempleRepository::class, $repo);

        $this->postJson('/api/v1/exemples', ['libelle' => 'Doublon', 'montant' => 10])
            ->assertStatus(422)
            ->assertExactJson(['success' => false, 'message' => 'Ce libellé existe déjà.']);
    }

    public function test_erreur_technique_masquee_500(): void
    {
        config(['app.debug' => false]);
        $repo = Mockery::mock(ExempleRepository::class);
        $repo->shouldReceive('lister')->andThrow(new RuntimeException('SQLSTATE[HY000] connexion refusée'));
        $this->app->instance(ExempleRepository::class, $repo);

        $this->getJson('/api/v1/exemples')
            ->assertStatus(500)
            ->assertExactJson(['success' => false, 'message' => 'Erreur interne du serveur.']);
    }

    public function test_reponse_json_meme_sans_entete_accept(): void
    {
        // Certains clients (mobile…) n'envoient pas toujours Accept: application/json.
        $this->post('/api/v1/exemples', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
