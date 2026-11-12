<?php

namespace App\Api\Controllers\V1;

use App\Api\Controllers\Controller;
use App\Api\Requests\Exemple\CreerExempleRequest;
use App\Api\Requests\Exemple\ListerExemplesRequest;
use App\Api\Responses\ApiResponse;
use App\Application\Exemple\UseCases\CreerExempleUseCase;
use App\Application\Exemple\UseCases\ListerExemplesUseCase;
use App\Application\Exemple\UseCases\ObtenirExempleUseCase;
use App\Domain\Exemple\Entities\Exemple;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Contrôleur modèle : valider (FormRequest) → cas d'usage → ApiResponse.
 * Aucune logique métier ni SQL ici.
 */
#[OA\Schema(
    schema: 'Exemple',
    required: ['id', 'libelle', 'montant'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'libelle', type: 'string', example: 'Recharge mobile'),
        new OA\Property(property: 'montant', type: 'number', format: 'float', example: 1500.5),
    ],
)]
class ExempleController extends Controller
{
    #[OA\Get(
        path: '/exemples',
        summary: 'Lister les exemples (paginé)',
        tags: ['Exemples'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)),
            new OA\Parameter(name: 'par_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100, minimum: 1, default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'OK'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Exemple')),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationFailed', response: 422),
        ],
    )]
    public function index(ListerExemplesRequest $request, ListerExemplesUseCase $useCase): JsonResponse
    {
        $exemples = $useCase->execute(
            (int) $request->input('page', 1),
            (int) $request->input('par_page', 20),
        );

        return ApiResponse::success(array_map(fn (Exemple $e) => $e->toArray(), $exemples));
    }

    #[OA\Get(
        path: '/exemples/{id}',
        summary: "Détail d'un exemple",
        tags: ['Exemples'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'OK'),
                new OA\Property(property: 'data', ref: '#/components/schemas/Exemple'),
            ])),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function show(int $id, ObtenirExempleUseCase $useCase): JsonResponse
    {
        return ApiResponse::success($useCase->execute($id)->toArray());
    }

    #[OA\Post(
        path: '/exemples',
        summary: 'Créer un exemple',
        tags: ['Exemples'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['libelle', 'montant'],
            properties: [
                new OA\Property(property: 'libelle', type: 'string', maxLength: 255, example: 'Recharge mobile'),
                new OA\Property(property: 'montant', type: 'number', minimum: 0, example: 1500),
            ],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Créé avec succès.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/Exemple'),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationFailed', response: 422),
        ],
    )]
    public function store(CreerExempleRequest $request, CreerExempleUseCase $useCase): JsonResponse
    {
        return ApiResponse::created($useCase->execute($request->toInput())->toArray());
    }
}
