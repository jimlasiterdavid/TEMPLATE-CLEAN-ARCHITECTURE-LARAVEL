<?php

namespace App\Infrastructure\Exemple\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent : détail de persistance, jamais exposé hors de l'Infrastructure.
 * Le repository le convertit en entité du domaine.
 *
 * @property int $id
 * @property string $libelle
 * @property string $montant
 */
class ExempleModel extends Model
{
    protected $table = 'exemples';

    protected $fillable = ['libelle', 'montant'];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }
}
