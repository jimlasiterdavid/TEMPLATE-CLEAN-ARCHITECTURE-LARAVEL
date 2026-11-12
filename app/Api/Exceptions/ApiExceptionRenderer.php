<?php

namespace App\Api\Exceptions;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\Errors\NotFoundError;
use App\Infrastructure\Database\StoredProcedure\StoredProcedureException;
use App\Api\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Convertit toute exception levée sur /api/* au format ApiResponse.
 */
final class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (Throwable $e, Request $request) => $request->is('api/*')
            ? self::toResponse($e)
            : null);
    }

    public static function toResponse(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => ApiResponse::error('Les données envoyées sont invalides.', 422, $e->errors()),
            $e instanceof BusinessRuleViolation => ApiResponse::error($e->getMessage(), 422),
            $e instanceof NotFoundError => ApiResponse::error($e->getMessage(), 404),
            $e instanceof AuthenticationException => ApiResponse::error('Non authentifié.', 401),
            $e instanceof NotFoundHttpException => ApiResponse::error('Ressource introuvable.', 404),
            $e instanceof HttpExceptionInterface => ApiResponse::error($e->getMessage() ?: 'Erreur HTTP.', $e->getStatusCode()),
            $e instanceof StoredProcedureException => ApiResponse::error(self::debugOr($e, 'Erreur lors du traitement de la requête.'), 500),
            default => ApiResponse::error(self::debugOr($e, 'Erreur interne du serveur.'), 500),
        };
    }

    private static function debugOr(Throwable $e, string $message): string
    {
        return config('app.debug') ? $e->getMessage() : $message;
    }
}
