<?php

namespace Tests\Feature\Exemple;

use App\Domain\Exemple\Repositories\ExempleRepository;
use Tests\Support\Fakes\InMemoryExempleRepository;
use Tests\TestCase;

/**
 * Tests fonctionnels : scénarios utilisateur complets enchaînant plusieurs appels,
 * toutes couches réelles sauf le stockage (repository en mémoire).
 */
class GestionExemplesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(ExempleRepository::class, new InMemoryExempleRepository);
    }

    public function test_creer_puis_consulter_puis_lister(): void
    {
        $id = $this->postJson('/api/v1/exemples', ['libelle' => '  Recharge mobile ', 'montant' => '1999.999'])
            ->assertCreated()
            ->json('data.id');

        // Le libellé est nettoyé et le montant arrondi par le domaine.
        $this->getJson("/api/v1/exemples/{$id}")
            ->assertOk()
            ->assertJsonPath('data.libelle', 'Recharge mobile')
            ->assertJsonPath('data.montant', 2000);

        $this->getJson('/api/v1/exemples')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_parcourir_les_pages(): void
    {
        foreach (['A', 'B', 'C', 'D', 'E'] as $libelle) {
            $this->postJson('/api/v1/exemples', ['libelle' => $libelle, 'montant' => 10])->assertCreated();
        }

        $pages = collect([1, 2, 3])->map(fn ($page) => collect(
            $this->getJson("/api/v1/exemples?page={$page}&par_page=2")->json('data')
        )->pluck('libelle')->all());

        $this->assertSame([['A', 'B'], ['C', 'D'], ['E']], $pages->all());
    }

    public function test_une_creation_refusee_ne_laisse_aucune_trace(): void
    {
        $this->postJson('/api/v1/exemples', ['libelle' => '   ', 'montant' => 10])
            ->assertStatus(422);

        $this->getJson('/api/v1/exemples')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
