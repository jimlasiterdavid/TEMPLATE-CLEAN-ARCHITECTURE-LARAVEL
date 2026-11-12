<?php

// @feature ui:livewire
use App\Web\Livewire\Exemples\Index as ExemplesIndex;
// @endfeature
// @feature ui:inertia
use App\Web\Controllers\ExempleController;
// @endfeature
use Illuminate\Support\Facades\Route;

// @feature ui
Route::redirect('/', '/exemples');

// @endfeature
// @feature ui:livewire
// Module modèle à dupliquer pour chaque ressource (composant Livewire pleine page).
Route::get('/exemples', ExemplesIndex::class)->name('exemples.index');
// @endfeature
// @feature ui:inertia
// Module modèle à dupliquer pour chaque ressource (pages Inertia).
Route::get('/exemples', [ExempleController::class, 'index'])->name('exemples.index');
Route::post('/exemples', [ExempleController::class, 'store'])->name('exemples.store');
// @endfeature
// @feature !ui & api
Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'api' => url('/api/v1'),
    'documentation' => url('/documentation'),
]));
// @endfeature
// @feature !ui & !api
Route::get('/', fn () => response()->json(['name' => config('app.name')]));
// @endfeature
