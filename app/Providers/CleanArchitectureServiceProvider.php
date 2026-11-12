<?php

namespace App\Providers;

use App\Domain\Exemple\Repositories\ExempleRepository;
// @feature data:eloquent
use App\Infrastructure\Exemple\Repositories\EloquentExempleRepository;
// @endfeature
// @feature data:procedures
use App\Infrastructure\Database\StoredProcedure\PdoProcedureExecutor;
use App\Infrastructure\Database\StoredProcedure\ProcedureExecutor;
use App\Infrastructure\Exemple\Repositories\StoredProcedureExempleRepository;
// @endfeature
use Illuminate\Support\ServiceProvider;

/**
 * Relie les ports du domaine (interfaces) à leurs adaptateurs d'infrastructure.
 * Ajouter ici chaque nouveau repository.
 */
class CleanArchitectureServiceProvider extends ServiceProvider
{
    public array $bindings = [
        // @feature data:eloquent
        ExempleRepository::class => EloquentExempleRepository::class,
        // @endfeature
        // @feature data:procedures
        ProcedureExecutor::class => PdoProcedureExecutor::class,
        ExempleRepository::class => StoredProcedureExempleRepository::class,
        // @endfeature
    ];
}
