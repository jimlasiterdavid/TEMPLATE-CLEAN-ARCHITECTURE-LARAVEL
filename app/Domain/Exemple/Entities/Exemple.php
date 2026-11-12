<?php

namespace App\Domain\Exemple\Entities;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;

/**
 * Entité modèle : à dupliquer pour chaque objet métier.
 */
final class Exemple
{
    public function __construct(
        public readonly int $id,
        public readonly string $libelle,
        public readonly Montant $montant,
    ) {
        if (trim($libelle) === '') {
            throw new BusinessRuleViolation('Le libellé est obligatoire.');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'montant' => $this->montant->valeur,
        ];
    }
}
