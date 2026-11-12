<?php

namespace Tests\Application\Exemple;

use App\Application\Exemple\UseCases\ListerExemplesUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\InMemoryExempleRepository;

class ListerExemplesUseCaseTest extends TestCase
{
    public function test_retourne_la_page_demandee(): void
    {
        $repo = new InMemoryExempleRepository;
        foreach (range(1, 5) as $i) {
            $repo->ajouter("Exemple {$i}", $i * 100);
        }

        $resultat = (new ListerExemplesUseCase($repo))->execute(page: 2, parPage: 2);

        $this->assertSame(['Exemple 3', 'Exemple 4'], array_map(fn ($e) => $e->libelle, $resultat));
    }

    public function test_normalise_la_pagination(): void
    {
        $repo = new InMemoryExempleRepository;

        (new ListerExemplesUseCase($repo))->execute(page: 0, parPage: 5000);

        $this->assertSame([1, ListerExemplesUseCase::PAR_PAGE_MAX], $repo->dernierListage);
    }
}
