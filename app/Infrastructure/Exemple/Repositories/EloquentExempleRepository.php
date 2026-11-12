<?php

namespace App\Infrastructure\Exemple\Repositories;

use App\Domain\Exemple\Entities\Exemple;
use App\Domain\Exemple\Repositories\ExempleRepository;
use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\ValueObjects\Montant;
use App\Infrastructure\Exemple\Models\ExempleModel;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Adaptateur Eloquent : traduit les appels du domaine en requêtes
 * et les modèles retournés en entités.
 */
final class EloquentExempleRepository implements ExempleRepository
{
    public function lister(int $page, int $parPage): array
    {
        return ExempleModel::query()
            ->orderBy('id')
            ->forPage($page, $parPage)
            ->get()
            ->map(fn (ExempleModel $model) => $this->toEntity($model))
            ->all();
    }

    public function trouver(int $id): ?Exemple
    {
        $model = ExempleModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function creer(string $libelle, Montant $montant): Exemple
    {
        try {
            $model = ExempleModel::create([
                'libelle' => $libelle,
                'montant' => $montant->valeur,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw new BusinessRuleViolation('Ce libellé existe déjà.', 0, $e);
        }

        return $this->toEntity($model);
    }

    private function toEntity(ExempleModel $model): Exemple
    {
        return new Exemple(
            id: $model->id,
            libelle: $model->libelle,
            montant: Montant::de($model->montant),
        );
    }
}
