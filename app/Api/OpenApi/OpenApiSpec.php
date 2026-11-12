<?php

namespace App\Api\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Définition globale de la documentation OpenAPI (Swagger UI : /documentation).
 * Chaque contrôleur documente ses routes avec des attributs #[OA\...].
 */
#[OA\Info(
    version: '1.0.0',
    title: 'TemplateApp API',
    description: 'API REST TemplateApp.',
    contact: new OA\Contact(name: 'TemplateApp'),
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST.'/api/v1', description: 'API v1')]
#[OA\Tag(name: 'Système', description: 'Supervision')]
#[OA\Tag(name: 'Exemples', description: 'Module modèle à dupliquer pour chaque ressource')]
#[OA\Schema(
    schema: 'ApiError',
    required: ['success', 'message'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Ce libellé existe déjà.'),
    ],
)]
#[OA\Schema(
    schema: 'ValidationError',
    required: ['success', 'message', 'errors'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Les données envoyées sont invalides.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['libelle' => ['Le champ libelle est obligatoire.']],
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
        ),
    ],
)]
#[OA\Response(response: 'NotFound', description: 'Ressource introuvable', content: new OA\JsonContent(ref: '#/components/schemas/ApiError'))]
#[OA\Response(response: 'BusinessError', description: 'Erreur métier (levée par la procédure stockée)', content: new OA\JsonContent(ref: '#/components/schemas/ApiError'))]
#[OA\Response(response: 'ValidationFailed', description: 'Données invalides', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
#[OA\Response(response: 'ServerError', description: 'Erreur interne', content: new OA\JsonContent(ref: '#/components/schemas/ApiError'))]
final class OpenApiSpec {}
