<?php

namespace App\Web\Exceptions;

use App\Domain\Shared\Errors\BusinessRuleViolation;
use App\Domain\Shared\Errors\NotFoundError;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Traduit les erreurs du domaine pour les pages web (hors /api) :
 *  - règle métier non respectée → retour au formulaire avec le message dans l'erreur « erreur » ;
 *  - ressource introuvable      → page 404.
 */
final class WebExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (BusinessRuleViolation $e, Request $request) {
            if (self::isApi($request)) {
                return null;
            }

            return back()->withInput()->withErrors(['erreur' => $e->getMessage()]);
        });

        $exceptions->render(function (NotFoundError $e, Request $request) {
            if (self::isApi($request)) {
                return null;
            }

            return app(ExceptionHandler::class)->render($request, new NotFoundHttpException($e->getMessage(), $e));
        });
    }

    private static function isApi(Request $request): bool
    {
        return $request->is('api/*') || ($request->expectsJson() && ! $request->header('X-Inertia'));
    }
}
