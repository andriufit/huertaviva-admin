<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

session_name('HVADMIN');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => config()['cookie_secure'],
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

function e(string|int|float|null $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function usuario_actual(): ?string
{
    return $_SESSION['usuario'] ?? null;
}

function exigir_login(): void
{
    if (usuario_actual() === null) {
        header('Location: login.php');
        exit;
    }
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(token_csrf()) . '">';
}

function comprobar_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Petición no válida.');
    }
}

function redirigir(string $destino): never
{
    header('Location: ' . $destino);
    exit;
}

function cabecera(string $titulo): void
{
    $usuario = usuario_actual();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo) ?> · Huerta Viva Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <header class="barra">
    <strong>Huerta Viva · Admin</strong>
    <?php if ($usuario !== null): ?>
      <nav>
        <a href="index.php">Pedidos</a>
        <a href="productos.php">Productos</a>
        <form method="post" action="logout.php" class="en-linea">
          <?= campo_csrf() ?>
          <button type="submit" class="enlace">Salir (<?= e($usuario) ?>)</button>
        </form>
      </nav>
    <?php endif; ?>
  </header>
  <main>
    <h1><?= e($titulo) ?></h1>
    <?php
}

function pie(): void
{
    ?>
  </main>
</body>
</html>
    <?php
}
