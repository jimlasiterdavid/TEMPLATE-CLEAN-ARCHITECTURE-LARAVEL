<?php

/*
| Seules les clés modifiées sont déclarées ici : Laravel les fusionne avec la
| configuration par défaut de Livewire (php artisan vendor:publish --tag=livewire:config
| pour la voir en entier).
*/

return [

    // Les composants Livewire font partie de la couche de présentation Web.
    'class_namespace' => 'App\\Web\\Livewire',

    'class_path' => app_path('Web/Livewire'),

];
