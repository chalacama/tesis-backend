<?php

return [
    // Agregamos 'auth/*' por si acaso tus rutas no empiezan con 'api/'
    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:4200',
        'https://tesis-frontend-six.vercel.app', // Tu producción
        // Agrega la URL con el hash por si acaso entras desde ahí
        'https://tesis-frontend-qsb6yhc8b-chalacamas-projects.vercel.app', 
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // IMPORTANTE: Cambia esto a true para permitir logins complejos
    'supports_credentials' => true, 
];