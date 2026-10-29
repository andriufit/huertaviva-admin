<?php
declare(strict_types=1);

require __DIR__ . '/../src/web.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $_SESSION = [];
    session_destroy();
}

redirigir('login.php');
