<?php

namespace App\Application\Exemple\UseCases;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\NotFoundError;

final class ObtenirExempleUseCase
{
    public function __construct(private readonly ExempleRepository $repository) {}

    public function execute(int $id): Exemple
    {
        return $this->repository->trouver($id) ?? throw NotFoundError::for('Exemple', $id);
    }
}
