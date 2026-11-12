<?php

namespace Tests\Web\Exemple;

use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Web\Controllers\ExempleController;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\Support\Fakes\InMemoryExempleRepository;
use Tests\TestCase;

/**
 * Interface Inertia : toutes couches réelles sauf le stockage (repository en mémoire).
 */
class ExemplesInertiaTest extends TestCase
{
    private InMemoryExempleRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->repo = new InMemoryExempleRepository;
        $this->app->instance(ExempleRepository::class, $this->repo);
    }

    public function test_la_page_liste_les_exemples(): void
    {
        $this->repo->ajouter('Recharge', 1500);

        $this->get('/exemples')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Exemples/Index')
                ->has('exemples', 1)
                ->where('exemples.0.libelle', 'Recharge')
                ->where('page', 1)
                ->where('parPage', ExempleController::PAR_PAGE));
    }

    public function test_l_accueil_redirige_vers_les_exemples(): void
    {
        $this->get('/')->assertRedirect('/exemples');
    }

    public function test_creer_un_exemple(): void
    {
        $this->post('/exemples', ['libelle' => '  Recharge mobile ', 'montant' => '1999.999'])
            ->assertRedirect('/exemples')
            ->assertSessionHas('succes');

        $exemples = $this->repo->lister(1, 10);
        $this->assertCount(1, $exemples);
        $this->assertSame('Recharge mobile', $exemples[0]->libelle);
        $this->assertSame(2000.0, $exemples[0]->montant->valeur);
    }

    public function test_validation_des_champs(): void
    {
        $this->from('/exemples')
            ->post('/exemples', ['libelle' => '', 'montant' => '-5'])
            ->assertRedirect('/exemples')
            ->assertSessionHasErrors(['libelle', 'montant']);

        $this->assertCount(0, $this->repo->lister(1, 10));
    }

    public function test_une_erreur_metier_revient_au_formulaire(): void
    {
        $repo = Mockery::mock(ExempleRepository::class);
        $repo->allows('creer')->andThrow(new BusinessRuleViolation('Ce libellé existe déjà.'));
        $this->app->instance(ExempleRepository::class, $repo);

        $this->from('/exemples')
            ->post('/exemples', ['libelle' => 'Transfert', 'montant' => '10'])
            ->assertRedirect('/exemples')
            ->assertSessionHasErrors(['erreur' => 'Ce libellé existe déjà.']);
    }

    public function test_pagination(): void
    {
        foreach (range(1, ExempleController::PAR_PAGE + 2) as $i) {
            $this->repo->ajouter("Exemple {$i}", $i);
        }

        $this->get('/exemples?page=2')
            ->assertInertia(fn (Assert $page) => $page
                ->has('exemples', 2)
                ->where('page', 2));
    }
}
