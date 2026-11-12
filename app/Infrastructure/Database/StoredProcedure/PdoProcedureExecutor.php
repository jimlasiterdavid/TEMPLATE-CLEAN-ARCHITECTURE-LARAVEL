<?php

namespace App\Infrastructure\Database\StoredProcedure;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use Illuminate\Database\DatabaseManager;
use PDO;
use PDOException;
use PDOStatement;

final class PdoProcedureExecutor implements ProcedureExecutor
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly ProcedureCallBuilder $builder,
        private readonly ?string $connection = null,
    ) {}

    public function call(string $procedure, array $params = []): array
    {
        return $this->callMultiple($procedure, $params)[0] ?? [];
    }

    public function first(string $procedure, array $params = []): ?array
    {
        return $this->call($procedure, $params)[0] ?? null;
    }

    public function callMultiple(string $procedure, array $params = []): array
    {
        $conn = $this->db->connection($this->connection);
        $sql = $this->builder->build($conn->getDriverName(), $procedure, $params);

        try {
            $statement = $conn->getPdo()->prepare($sql);
            foreach (array_values($params) as $i => $value) {
                $statement->bindValue($i + 1, $value, $this->pdoType($value));
            }
            $statement->execute();

            $sets = [];
            do {
                if ($statement->columnCount() > 0) {
                    $sets[] = $statement->fetchAll(PDO::FETCH_ASSOC);
                }
            } while ($this->nextRowset($statement));

            $statement->closeCursor();

            return $sets;
        } catch (PDOException $e) {
            $error = StoredProcedureException::fromPdo($procedure, $e);

            throw $error->isBusinessError()
                ? new BusinessRuleViolation($error->driverMessage, 0, $error)
                : $error;
        }
    }

    /**
     * Typage explicite : sans lui, false est envoyé comme '' (refusé par PostgreSQL pour un BOOLEAN).
     */
    private function pdoType(mixed $value): int
    {
        return match (true) {
            \is_bool($value) => PDO::PARAM_BOOL,
            \is_int($value) => PDO::PARAM_INT,
            $value === null => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    /**
     * Certains drivers lèvent une exception au lieu de retourner false
     * lorsqu'il n'y a plus de jeu de résultats.
     */
    private function nextRowset(PDOStatement $statement): bool
    {
        try {
            return $statement->nextRowset();
        } catch (PDOException) {
            return false;
        }
    }
}
