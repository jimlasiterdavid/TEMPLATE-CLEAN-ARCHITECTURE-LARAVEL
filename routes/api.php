<?php

use App\Api\Controllers\V1\ExempleController;
use App\Api\Controllers\V1\HealthController;
use Illuminate\Support\Facades\Route;

/*
| Toutes les routes sont préfixées par /api (voir bootstrap/app.php).
| Versionnement : /api/v1/...
*/

Route::prefix('v1')->group(function () {
    Route::get('health', HealthController::class);

    // Module modèle à dupliquer pour chaque ressource exposée par procédure stockée.
    Route::get('exemples', [ExempleController::class, 'index']);
    Route::get('exemples/{id}', [ExempleController::class, 'show'])->whereNumber('id');
    Route::post('exemples', [ExempleController::class, 'store']);
});
