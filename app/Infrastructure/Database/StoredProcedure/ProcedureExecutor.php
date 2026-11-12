<?php

namespace App\Infrastructure\Database\StoredProcedure;

/**
 * Exécute des procédures stockées. Utilisé par les repositories de l'infrastructure.
 *
 * @throws \App\Domain\Shared\Errors\BusinessRuleViolation erreur métier levée par la procédure
 * @throws StoredProcedureException                        erreur technique
 */
interface ProcedureExecutor
{
    /** @return list<array<string, mixed>> premier jeu de résultats */
    public function call(string $procedure, array $params = []): array;

    /** @return array<string, mixed>|null première ligne du premier jeu de résultats */
    public function first(string $procedure, array $params = []): ?array;

    /** @return list<list<array<string, mixed>>> tous les jeux de résultats */
    public function callMultiple(string $procedure, array $params = []): array;
}
