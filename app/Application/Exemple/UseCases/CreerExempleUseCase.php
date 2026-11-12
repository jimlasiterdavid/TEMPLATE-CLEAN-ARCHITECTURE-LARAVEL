<?php

namespace App\Application\Exemple\UseCases;

use App\Application\Exemple\DTO\CreerExempleInput;
use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;

final class CreerExempleUseCase
{
    public function __construct(private readonly ExempleRepository $repository) {}

    public function execute(CreerExempleInput $input): Exemple
    {
        $libelle = trim($input->libelle);

        if ($libelle === '') {
            throw new BusinessRuleViolation('Le libellé est obligatoire.');
        }

        return $this->repository->creer($libelle, Montant::de($input->montant));
    }
}
