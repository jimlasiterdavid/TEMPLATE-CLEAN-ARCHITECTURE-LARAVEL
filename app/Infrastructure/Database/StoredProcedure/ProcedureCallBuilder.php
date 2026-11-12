<?php

namespace App\Infrastructure\Database\StoredProcedure;

use InvalidArgumentException;

/**
 * Construit l'instruction d'appel d'une procédure stockée.
 *
 * PostgreSQL (moteur du projet) : les « procédures » qui retournent des données sont des
 * fonctions (RETURNS TABLE / SETOF) appelées par SELECT * FROM fn(...).
 * MySQL et SQL Server restent pris en charge au cas où une autre base serait branchée.
 *
 * Paramètres :
 *  - liste       [1, 20]                          → passés dans l'ordre
 *  - associatif  ['p_page' => 1, 'p_taille' => 20] → paramètres nommés
 *                (PostgreSQL : p_page => ?, SQL Server : @p_page = ?)
 */
final class ProcedureCallBuilder
{
    public function build(string $driver, string $procedure, array $params = []): string
    {
        $this->assertValidName($procedure);

        return match ($driver) {
            'pgsql' => "SELECT * FROM {$procedure}({$this->args($params, '%s => ?')})",
            'sqlsrv' => rtrim("SET NOCOUNT ON; EXEC {$procedure} {$this->args($params, '@%s = ?')}"),
            'mysql', 'mariadb' => "CALL {$procedure}({$this->args($params, null)})",
            default => throw new InvalidArgumentException("Driver [{$driver}] non pris en charge pour les procédures stockées."),
        };
    }

    /**
     * Placeholders positionnels, ou nommés selon $namedFormat si les paramètres sont associatifs.
     */
    private function args(array $params, ?string $namedFormat): string
    {
        if (array_is_list($params) || $namedFormat === null) {
            return implode(', ', array_fill(0, \count($params), '?'));
        }

        return implode(', ', array_map(function (string $key) use ($namedFormat) {
            $this->assertValidName($key);

            return \sprintf($namedFormat, $key);
        }, array_keys($params)));
    }

    /**
     * Le nom est injecté dans le SQL : on n'accepte que des identifiants
     * (avec schéma optionnel, ex. paiement.ps_liste_transactions).
     */
    private function assertValidName(string $name): void
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $name)) {
            throw new InvalidArgumentException("Nom de procédure ou de paramètre invalide : [{$name}].");
        }
    }
}
