<?php

namespace App\Providers;

use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Infrastructure\Database\StoredProcedure\PdoProcedureExecutor;
use App\Infrastructure\Database\StoredProcedure\ProcedureExecutor;
use App\Infrastructure\Exemple\Repositories\StoredProcedureExempleRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Relie les ports du domaine (interfaces) à leurs adaptateurs d'infrastructure.
 * Ajouter ici chaque nouveau repository.
 */
class CleanArchitectureServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ProcedureExecutor::class => PdoProcedureExecutor::class,
        ExempleRepository::class => StoredProcedureExempleRepository::class,
    ];
}
