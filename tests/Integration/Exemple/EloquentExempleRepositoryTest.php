<?php

namespace Tests\Integration\Exemple;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;
use App\Infrastructure\Exemple\Repositories\EloquentExempleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Repository Eloquent contre une vraie base (SQLite en mémoire, voir phpunit.xml)
 * avec les migrations du projet.
 */
class EloquentExempleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ExempleRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(ExempleRepository::class);
    }

    public function test_le_conteneur_fournit_le_repository_eloquent(): void
    {
        $this->assertInstanceOf(EloquentExempleRepository::class, $this->repo);
    }

    public function test_creer_mappe_le_modele_en_entite(): void
    {
        $exemple = $this->repo->creer('Paiement marchand', Montant::de(2500.75));

        $this->assertInstanceOf(Exemple::class, $exemple);
        $this->assertGreaterThan(0, $exemple->id);
        $this->assertSame('Paiement marchand', $exemple->libelle);
        $this->assertSame(2500.75, $exemple->montant->valeur);
        $this->assertDatabaseHas('exemples', ['libelle' => 'Paiement marchand']);
    }

    public function test_trouver_et_lister(): void
    {
        $cree = $this->repo->creer('A', Montant::de(1));
        $this->repo->creer('B', Montant::de(2));
        $this->repo->creer('C', Montant::de(3));

        $this->assertSame('A', $this->repo->trouver($cree->id)?->libelle);
        $this->assertNull($this->repo->trouver(99999));
        $this->assertSame(['C'], array_map(fn ($e) => $e->libelle, $this->repo->lister(2, 2)));
    }

    public function test_libelle_en_double_devient_une_erreur_metier(): void
    {
        $this->repo->creer('Transfert', Montant::de(5000));

        $this->expectException(BusinessRuleViolation::class);
        $this->expectExceptionMessage('Ce libellé existe déjà.');

        $this->repo->creer('Transfert', Montant::de(1));
    }
    // @feature api

    public function test_bout_en_bout_http_vers_la_base(): void
    {
        $this->postJson('/api/v1/exemples', ['libelle' => 'Transfert', 'montant' => 5000])
            ->assertCreated()
            ->assertJsonPath('data.libelle', 'Transfert');

        $this->postJson('/api/v1/exemples', ['libelle' => 'Transfert', 'montant' => 1])
            ->assertStatus(422)
            ->assertExactJson(['success' => false, 'message' => 'Ce libellé existe déjà.']);
    }
    // @endfeature
}
