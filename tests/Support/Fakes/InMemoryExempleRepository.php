<?php

namespace Tests\Support\Fakes;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\ValueObjects\Montant;

/**
 * Repository en mémoire : remplace les procédures stockées dans les tests
 * Application et Feature.
 */
final class InMemoryExempleRepository implements ExempleRepository
{
    /** @var array<int, Exemple> */
    private array $items = [];

    /** Derniers arguments reçus par lister(), pour vérifier la normalisation. */
    public ?array $dernierListage = null;

    public function ajouter(string $libelle, float $montant): Exemple
    {
        return $this->creer($libelle, Montant::de($montant));
    }

    public function lister(int $page, int $parPage): array
    {
        $this->dernierListage = [$page, $parPage];

        return array_values(\array_slice($this->items, ($page - 1) * $parPage, $parPage));
    }

    public function trouver(int $id): ?Exemple
    {
        return $this->items[$id] ?? null;
    }

    public function creer(string $libelle, Montant $montant): Exemple
    {
        $id = \count($this->items) + 1;

        return $this->items[$id] = new Exemple($id, $libelle, $montant);
    }
}
