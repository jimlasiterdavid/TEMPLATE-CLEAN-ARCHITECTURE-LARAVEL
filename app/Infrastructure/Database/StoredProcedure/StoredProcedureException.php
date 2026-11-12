<?php

namespace App\Infrastructure\Database\StoredProcedure;

use PDOException;
use RuntimeException;

/**
 * Erreur levée lors de l'exécution d'une procédure stockée.
 *
 * Sont considérées comme erreurs « métier » (message renvoyé à l'utilisateur, HTTP 422) :
 *  - PostgreSQL    : RAISE EXCEPTION '...'  (SQLSTATE P0001, valeur par défaut)
 *  - MySQL/MariaDB : SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '...'
 *  - SQL Server    : THROW 50001, '...', 1  (numéro d'erreur >= 50000)
 *
 * Les autres (procédure absente, erreur SQL…) sont techniques (HTTP 500).
 */
class StoredProcedureException extends RuntimeException
{
    public function __construct(
        public readonly string $procedure,
        public readonly ?string $sqlState,
        public readonly ?int $driverCode,
        public readonly string $driverMessage,
        ?PDOException $previous = null,
    ) {
        parent::__construct("[{$procedure}] {$driverMessage}", 0, $previous);
    }

    public static function fromPdo(string $procedure, PDOException $e): self
    {
        $info = $e->errorInfo ?? [];

        return new self(
            $procedure,
            $info[0] ?? (\is_string($e->getCode()) ? $e->getCode() : null),
            isset($info[1]) ? (int) $info[1] : null,
            self::cleanMessage((string) ($info[2] ?? $e->getMessage())),
            $e,
        );
    }

    /**
     * PostgreSQL renvoie « ERROR:  message\nCONTEXT:  PL/pgSQL function … » :
     * on ne garde que le message destiné à l'utilisateur.
     */
    private static function cleanMessage(string $message): string
    {
        $firstLine = strtok($message, "\n") ?: $message;

        return trim(preg_replace('/^(ERROR|ERREUR)\s*:\s*/u', '', $firstLine));
    }

    public function isBusinessError(): bool
    {
        return \in_array($this->sqlState, ['45000', 'P0001'], true)
            || ($this->driverCode !== null && $this->driverCode >= 50000);
    }
}
