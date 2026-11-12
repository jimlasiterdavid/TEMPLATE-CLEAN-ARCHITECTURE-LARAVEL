<?php

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\Database\StoredProcedure\ProcedureCallBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProcedureCallBuilderTest extends TestCase
{
    private ProcedureCallBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ProcedureCallBuilder;
    }

    public function test_mysql_et_mariadb(): void
    {
        $this->assertSame('CALL ps_liste(?, ?)', $this->builder->build('mysql', 'ps_liste', [1, 20]));
        $this->assertSame('CALL ps_liste()', $this->builder->build('mariadb', 'ps_liste'));
    }

    public function test_sqlserver_positionnel_et_nomme(): void
    {
        $this->assertSame('SET NOCOUNT ON; EXEC dbo.ps_liste', $this->builder->build('sqlsrv', 'dbo.ps_liste'));
        $this->assertSame('SET NOCOUNT ON; EXEC dbo.ps_liste ?, ?', $this->builder->build('sqlsrv', 'dbo.ps_liste', [1, 20]));
        $this->assertSame(
            'SET NOCOUNT ON; EXEC ps_liste @page = ?, @par_page = ?',
            $this->builder->build('sqlsrv', 'ps_liste', ['page' => 1, 'par_page' => 20]),
        );
    }

    public function test_postgresql_positionnel(): void
    {
        $this->assertSame('SELECT * FROM ps_liste()', $this->builder->build('pgsql', 'ps_liste'));
        $this->assertSame('SELECT * FROM paiement.ps_liste(?, ?)', $this->builder->build('pgsql', 'paiement.ps_liste', [1, 20]));
    }

    public function test_postgresql_parametres_nommes(): void
    {
        $this->assertSame(
            'SELECT * FROM ps_liste(p_page => ?, p_par_page => ?)',
            $this->builder->build('pgsql', 'ps_liste', ['p_page' => 1, 'p_par_page' => 20]),
        );
    }

    public function test_driver_non_supporte(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->build('sqlite', 'ps_liste');
    }

    #[DataProvider('nomsDangereux')]
    public function test_refuse_les_injections_dans_les_noms(string $procedure, array $params): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->build('pgsql', $procedure, $params);
    }

    public static function nomsDangereux(): array
    {
        return [
            'procédure' => ['ps_liste; DROP TABLE x; --', []],
            'espace' => ['ps liste', []],
            'paramètre nommé' => ['ps_liste', ['id = 1; --' => 1]],
        ];
    }
}
