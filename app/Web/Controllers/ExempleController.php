<?php

namespace App\Web\Controllers;

use App\Application\Exemple\UseCases\CreerExempleUseCase;
use App\Application\Exemple\UseCases\ListerExemplesUseCase;
use App\Domain\Exemple\Entities\Exemple;
use App\Web\Requests\Exemple\CreerExempleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Contrôleur modèle : valider (FormRequest) → cas d'usage → page Inertia.
 * Aucune logique métier ni accès aux données ici.
 * Les erreurs du domaine sont traduites par App\Web\Exceptions\WebExceptionRenderer.
 */
class ExempleController
{
    public const PAR_PAGE = 10;

    public function index(Request $request, ListerExemplesUseCase $useCase): Response
    {
        $page = max(1, $request->integer('page', 1));

        return Inertia::render('Exemples/Index', [
            'exemples' => array_map(
                fn (Exemple $exemple) => $exemple->toArray(),
                $useCase->execute($page, self::PAR_PAGE),
            ),
            'page' => $page,
            'parPage' => self::PAR_PAGE,
        ]);
    }

    public function store(CreerExempleRequest $request, CreerExempleUseCase $useCase): RedirectResponse
    {
        $useCase->execute($request->toInput());

        return to_route('exemples.index')->with('succes', 'Exemple créé.');
    }
}
