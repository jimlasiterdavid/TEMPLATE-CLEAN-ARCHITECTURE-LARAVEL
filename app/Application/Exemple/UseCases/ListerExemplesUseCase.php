<?php

namespace App\Application\Exemple\UseCases;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;

final class ListerExemplesUseCase
{
    public const PAR_PAGE_MAX = 100;

    public function __construct(private readonly ExempleRepository $repository) {}

    /** @return list<Exemple> */
    public function execute(int $page = 1, int $parPage = 20): array
    {
        return $this->repository->lister(
            max(1, $page),
            min(self::PAR_PAGE_MAX, max(1, $parPage)),
        );
    }
}
