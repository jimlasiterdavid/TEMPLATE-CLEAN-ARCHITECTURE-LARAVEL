<?php

namespace App\Api\Controllers\V1;

use App\Api\Controllers\Controller;
use App\Api\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Throwable;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/health',
        summary: "État de l'API et de la base",
        tags: ['Système'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'OK'),
                new OA\Property(property: 'data', properties: [
                    new OA\Property(property: 'app', type: 'string', example: 'TemplateApp'),
                    new OA\Property(property: 'version', type: 'string', example: 'v1'),
                    new OA\Property(property: 'database', type: 'string', enum: ['ok', 'indisponible']),
                    new OA\Property(property: 'time', type: 'string', format: 'date-time'),
                ], type: 'object'),
            ])),
        ],
    )]
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $database = 'ok';
        } catch (Throwable) {
            $database = 'indisponible';
        }

        return ApiResponse::success([
            'app' => config('app.name'),
            'version' => 'v1',
            'database' => $database,
            'time' => now()->toIso8601String(),
        ]);
    }
}
