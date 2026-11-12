<?php

namespace Tests\Integration\StoredProcedure;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Infrastructure\Database\StoredProcedure\ProcedureExecutor;
use App\Infrastructure\Database\StoredProcedure\StoredProcedureException;
use Tests\Integration\IntegrationTestCase;

class PdoProcedureExecutorTest extends IntegrationTestCase
{
    private ProcedureExecutor $sp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sp = $this->app->make(ProcedureExecutor::class);
    }

    public function test_call_retourne_les_lignes(): void
    {
        $this->sp->call('ps_exemple_creer', ['Recharge', 1500]);

        $lignes = $this->sp->call('ps_exemple_liste', [1, 10]);

        $this->assertCount(1, $lignes);
        $this->assertSame('Recharge', $lignes[0]['libelle']);
    }

    public function test_first_retourne_null_si_aucune_ligne(): void
    {
        $this->assertNull($this->sp->first('ps_exemple_detail', [999]));
    }

    public function test_parametres_nommes(): void
    {
        $this->sp->call('ps_exemple_creer', ['p_montant' => 10, 'p_libelle' => 'Nommé']);

        $this->assertSame('Nommé', $this->sp->first('ps_exemple_detail', ['p_id' => 1])['libelle']);
    }

    public function test_typage_booleen_et_null(): void
    {
        $this->assertSame(
            ['actif' => false, 'note_absente' => true],
            $this->sp->first('ps_test_types', [false, null]),
        );
    }

    public function test_raise_exception_devient_erreur_metier(): void
    {
        $this->sp->call('ps_exemple_creer', ['Doublon', 10]);

        $this->expectException(BusinessRuleViolation::class);
        $this->expectExceptionMessage('Ce libellé existe déjà.');

        $this->sp->call('ps_exemple_creer', ['Doublon', 20]);
    }

    public function test_fonction_inexistante_devient_erreur_technique(): void
    {
        try {
            $this->sp->call('ps_inexistante');
            $this->fail('StoredProcedureException attendue');
        } catch (StoredProcedureException $e) {
            $this->assertFalse($e->isBusinessError());
            $this->assertSame('42883', $e->sqlState);
        }
    }
}
