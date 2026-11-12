<?php

namespace Tests\Application\Exemple;

use App\Application\Exemple\DTO\CreerExempleInput;
use App\Application\Exemple\UseCases\CreerExempleUseCase;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\InMemoryExempleRepository;

class CreerExempleUseCaseTest extends TestCase
{
    public function test_cree_avec_libelle_nettoye_et_montant_arrondi(): void
    {
        $repo = new InMemoryExempleRepository;

        $exemple = (new CreerExempleUseCase($repo))->execute(new CreerExempleInput('  Paiement  ', '99.999'));

        $this->assertSame('Paiement', $exemple->libelle);
        $this->assertSame(100.0, $exemple->montant->valeur);
        $this->assertSame($exemple, $repo->trouver($exemple->id));
    }

    public function test_refuse_un_montant_negatif_sans_appeler_le_repository(): void
    {
        $repo = new InMemoryExempleRepository;

        try {
            (new CreerExempleUseCase($repo))->execute(new CreerExempleInput('Paiement', -10));
            $this->fail('BusinessRuleViolation attendue');
        } catch (BusinessRuleViolation) {
            $this->assertNull($repo->trouver(1));
        }
    }

    public function test_refuse_un_libelle_vide(): void
    {
        $this->expectException(BusinessRuleViolation::class);

        (new CreerExempleUseCase(new InMemoryExempleRepository))->execute(new CreerExempleInput('  ', 10));
    }
}
