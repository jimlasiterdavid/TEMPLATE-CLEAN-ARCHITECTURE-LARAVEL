<?php

namespace Tests\Web\Exemple;

use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Web\Livewire\Exemples\Index;
use Livewire\Livewire;
use Mockery;
use Tests\Support\Fakes\InMemoryExempleRepository;
use Tests\TestCase;

/**
 * Interface Livewire : toutes couches réelles sauf le stockage (repository en mémoire).
 */
class ExemplesLivewireTest extends TestCase
{
    private InMemoryExempleRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->repo = new InMemoryExempleRepository;
        $this->app->instance(ExempleRepository::class, $this->repo);
    }

    public function test_la_page_s_affiche(): void
    {
        $this->repo->ajouter('Recharge', 1500);

        $this->get('/exemples')
            ->assertOk()
            ->assertSeeLivewire(Index::class)
            ->assertSee('Recharge');
    }

    public function test_l_accueil_redirige_vers_les_exemples(): void
    {
        $this->get('/')->assertRedirect('/exemples');
    }

    public function test_creer_un_exemple(): void
    {
        Livewire::test(Index::class)
            ->set('libelle', '  Recharge mobile ')
            ->set('montant', '1999.999')
            ->call('creer')
            ->assertHasNoErrors()
            ->assertSet('libelle', '')
            ->assertSee('Recharge mobile')
            ->assertSee('2 000,00');

        $this->assertCount(1, $this->repo->lister(1, 10));
    }

    public function test_validation_des_champs(): void
    {
        Livewire::test(Index::class)
            ->set('libelle', '')
            ->set('montant', '-5')
            ->call('creer')
            ->assertHasErrors(['libelle', 'montant']);

        $this->assertCount(0, $this->repo->lister(1, 10));
    }

    public function test_une_erreur_metier_est_affichee(): void
    {
        $repo = Mockery::mock(ExempleRepository::class);
        $repo->allows('lister')->andReturn([]);
        $repo->allows('creer')->andThrow(new BusinessRuleViolation('Ce libellé existe déjà.'));
        $this->app->instance(ExempleRepository::class, $repo);

        Livewire::test(Index::class)
            ->set('libelle', 'Transfert')
            ->set('montant', '10')
            ->call('creer')
            ->assertHasErrors('erreur')
            ->assertSee('Ce libellé existe déjà.');
    }

    public function test_pagination(): void
    {
        foreach (range(1, Index::PAR_PAGE + 2) as $i) {
            $this->repo->ajouter("Exemple {$i}", $i);
        }

        Livewire::test(Index::class)
            ->assertSee('Exemple 1')
            ->call('pageSuivante')
            ->assertSet('page', 2)
            ->assertSee('Exemple '.(Index::PAR_PAGE + 1))
            ->assertDontSee('Exemple 3');
    }
}
