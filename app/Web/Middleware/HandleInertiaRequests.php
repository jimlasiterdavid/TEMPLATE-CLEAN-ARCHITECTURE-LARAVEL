<?php

namespace App\Web\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Vue Blade racine chargée à la première visite (resources/views/app.blade.php).
     */
    protected $rootView = 'app';

    /**
     * Données partagées avec toutes les pages.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'flash' => [
                'succes' => fn () => $request->session()->get('succes'),
            ],
        ];
    }
}
