<?php

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Errors\BusinessRuleViolation;

/**
 * Montant positif ou nul, arrondi à 2 décimales.
 */
final class Montant
{
    private function __construct(public readonly float $valeur) {}

    public static function de(float|int|string $valeur): self
    {
        if (! is_numeric($valeur)) {
            throw new BusinessRuleViolation('Le montant doit être numérique.');
        }

        $valeur = round((float) $valeur, 2);

        if ($valeur < 0) {
            throw new BusinessRuleViolation('Le montant ne peut pas être négatif.');
        }

        return new self($valeur);
    }

    public function equals(self $autre): bool
    {
        return $this->valeur === $autre->valeur;
    }
}
