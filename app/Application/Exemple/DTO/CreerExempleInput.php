<?php

namespace App\Application\Exemple\DTO;

final class CreerExempleInput
{
    public function __construct(
        public readonly string $libelle,
        public readonly float|int|string $montant,
    ) {}
}
