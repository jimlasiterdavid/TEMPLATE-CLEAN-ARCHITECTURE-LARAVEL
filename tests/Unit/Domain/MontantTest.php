<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MontantTest extends TestCase
{
    public function test_arrondit_a_deux_decimales(): void
    {
        $this->assertSame(1500.13, Montant::de('1500.126')->valeur);
        $this->assertSame(0.0, Montant::de(0)->valeur);
    }

    public function test_egalite_par_valeur(): void
    {
        $this->assertTrue(Montant::de(10)->equals(Montant::de('10.00')));
        $this->assertFalse(Montant::de(10)->equals(Montant::de(10.01)));
    }

    #[DataProvider('montantsInvalides')]
    public function test_refuse_les_montants_invalides(mixed $valeur): void
    {
        $this->expectException(BusinessRuleViolation::class);
        Montant::de($valeur);
    }

    public static function montantsInvalides(): array
    {
        return [
            'négatif' => [-1],
            'négatif décimal' => ['-0.5'],
            'non numérique' => ['abc'],
        ];
    }
}
