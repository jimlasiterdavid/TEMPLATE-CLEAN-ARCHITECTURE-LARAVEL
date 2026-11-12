<?php

namespace App\Domain\Exemple\Repositories;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Shared\ValueObjects\Montant;

/**
 * Port du domaine : l'implémentation (procédures stockées) vit dans Infrastructure.
 */
interface ExempleRepository
{
    /** @return list<Exemple> */
    public function lister(int $page, int $parPage): array;

    public function trouver(int $id): ?Exemple;

    public function creer(string $libelle, Montant $montant): Exemple;
}
