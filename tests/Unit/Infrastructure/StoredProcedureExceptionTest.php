<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\Database\StoredProcedure\StoredProcedureException;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StoredProcedureExceptionTest extends TestCase
{
    #[DataProvider('erreurs')]
    public function test_classe_les_erreurs_metier_et_techniques(array $errorInfo, bool $metier): void
    {
        $pdo = new PDOException('erreur');
        $pdo->errorInfo = $errorInfo;

        $e = StoredProcedureException::fromPdo('ps_test', $pdo);

        $this->assertSame($metier, $e->isBusinessError());
        $this->assertSame($errorInfo[2], $e->driverMessage);
        $this->assertSame('ps_test', $e->procedure);
    }

    public function test_nettoie_le_message_postgresql(): void
    {
        $pdo = new PDOException('erreur');
        $pdo->errorInfo = ['P0001', 7, "ERROR:  Solde insuffisant\nCONTEXT:  PL/pgSQL function ps_payer(integer) line 5 at RAISE"];

        $this->assertSame('Solde insuffisant', StoredProcedureException::fromPdo('ps_payer', $pdo)->driverMessage);
    }

    public static function erreurs(): array
    {
        return [
            'postgresql fonction absente' => [['42883', 7, 'function ps_x() does not exist'], false],
            'mysql SIGNAL 45000' => [['45000', 1644, 'Solde insuffisant'], true],
            'sqlserver THROW 50001' => [['HY000', 50001, 'Solde insuffisant'], true],
            'postgresql RAISE' => [['P0001', 7, 'Solde insuffisant'], true],
            'procédure absente' => [['42000', 1305, 'PROCEDURE ps_x does not exist'], false],
            'erreur sql' => [['42S22', 1054, "Unknown column 'x'"], false],
        ];
    }
}
