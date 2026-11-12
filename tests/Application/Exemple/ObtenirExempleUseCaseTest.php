<?php

namespace Tests\Application\Exemple;

use App\Application\Exemple\UseCases\ObtenirExempleUseCase;
use App\Domain\Shared\Errors\NotFoundError;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\InMemoryExempleRepository;

class ObtenirExempleUseCaseTest extends TestCase
{
    public function test_retourne_l_exemple(): void
    {
        $repo = new InMemoryExempleRepository;
        $cree = $repo->ajouter('Recharge', 1000);

        $this->assertSame($cree, (new ObtenirExempleUseCase($repo))->execute($cree->id));
    }

    public function test_leve_not_found_si_absent(): void
    {
        $this->expectException(NotFoundError::class);
        $this->expectExceptionMessage('Exemple [42] introuvable.');

        (new ObtenirExempleUseCase(new InMemoryExempleRepository))->execute(42);
    }
}
