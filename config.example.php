<?php
// Copia este fichero como config.local.php y ajusta los valores.
// config.local.php no se sube al repositorio.

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'huertaviva',
        'user' => 'huerta_admin',
        'pass' => 'CAMBIA_ESTA_CONTRASEÑA',
    ],

    // Ponlo a true cuando el panel se sirva por HTTPS:
    // la cookie de sesión solo viajará por conexiones cifradas.
    'cookie_secure' => false,
];
