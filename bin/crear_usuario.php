<?php
// Crea un usuario del panel de administración.
// Uso: php bin/crear_usuario.php <usuario>
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require __DIR__ . '/../src/bootstrap.php';

$usuario = $argv[1] ?? '';
if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $usuario)) {
    fwrite(STDERR, "Uso: php bin/crear_usuario.php <usuario>\n");
    fwrite(STDERR, "El usuario debe tener entre 3 y 50 caracteres (letras, números, punto, guion o guion bajo).\n");
    exit(1);
}

// Oculta la contraseña mientras se escribe (Linux)
$ocultar = DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN);
echo 'Contraseña (mínimo 12 caracteres): ';
if ($ocultar) shell_exec('stty -echo');
$password = rtrim((string) fgets(STDIN), "\r\n");
if ($ocultar) shell_exec('stty echo');
echo "\n";

if (strlen($password) < 12) {
    fwrite(STDERR, "La contraseña debe tener al menos 12 caracteres.\n");
    exit(1);
}

try {
    $consulta = db()->prepare('INSERT INTO admin_usuarios (usuario, password_hash) VALUES (?, ?)');
    $consulta->execute([$usuario, password_hash($password, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    fwrite(STDERR, 'No se ha podido crear el usuario: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "Usuario '$usuario' creado.\n";
