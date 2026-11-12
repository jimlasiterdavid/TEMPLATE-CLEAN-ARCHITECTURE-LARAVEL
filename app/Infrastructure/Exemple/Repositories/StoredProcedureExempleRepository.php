<?php

namespace App\Infrastructure\Exemple\Repositories;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\ValueObjects\Montant;
use App\Infrastructure\Database\StoredProcedure\ProcedureExecutor;

/**
 * Adaptateur : traduit les appels du domaine en procédures stockées
 * et les lignes retournées en entités.
 */
final class StoredProcedureExempleRepository implements ExempleRepository
{
    public function __construct(private readonly ProcedureExecutor $sp) {}

    public function lister(int $page, int $parPage): array
    {
        return array_map(
            fn (array $row) => $this->toEntity($row),
            $this->sp->call('ps_exemple_liste', [$page, $parPage]),
        );
    }

    public function trouver(int $id): ?Exemple
    {
        $row = $this->sp->first('ps_exemple_detail', [$id]);

        return $row ? $this->toEntity($row) : null;
    }

    public function creer(string $libelle, Montant $montant): Exemple
    {
        return $this->toEntity(
            $this->sp->first('ps_exemple_creer', [$libelle, $montant->valeur]),
        );
    }

    private function toEntity(array $row): Exemple
    {
        return new Exemple(
            id: (int) $row['id'],
            libelle: (string) $row['libelle'],
            montant: Montant::de($row['montant']),
        );
    }
}
