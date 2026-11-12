<?php

namespace Tests\Unit\Domain;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;
use PHPUnit\Framework\TestCase;

class ExempleTest extends TestCase
{
    public function test_serialise_en_tableau(): void
    {
        $exemple = new Exemple(7, 'Recharge', Montant::de(2500));

        $this->assertSame(['id' => 7, 'libelle' => 'Recharge', 'montant' => 2500.0], $exemple->toArray());
    }

    public function test_refuse_un_libelle_vide(): void
    {
        $this->expectException(BusinessRuleViolation::class);
        new Exemple(1, '   ', Montant::de(1));
    }
}
