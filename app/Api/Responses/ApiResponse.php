<?php

namespace App\Api\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Format de réponse unique pour les fronts Angular et Flutter.
 *
 * Succès : { "success": true,  "message": "...", "data": ... }
 * Erreur : { "success": false, "message": "...", "errors": { ... } }
 */
final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function created(mixed $data = null, string $message = 'Créé avec succès.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}
