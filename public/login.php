<?php
declare(strict_types=1);

require __DIR__ . '/../src/web.php';

if (usuario_actual() !== null) {
    redirigir('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $consulta = db()->prepare('SELECT password_hash FROM admin_usuarios WHERE usuario = ?');
    $consulta->execute([$usuario]);
    $fila = $consulta->fetch();

    if ($fila && password_verify($password, $fila['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        redirigir('index.php');
    }

    $error = 'Usuario o contraseña incorrectos.';
}

cabecera('Acceso');
?>
<form method="post" class="formulario">
  <?= campo_csrf() ?>
  <?php if ($error !== null): ?>
    <p class="aviso aviso-error"><?= e($error) ?></p>
  <?php endif; ?>
  <label>Usuario <input type="text" name="usuario" required autofocus></label>
  <label>Contraseña <input type="password" name="password" required></label>
  <button type="submit">Entrar</button>
</form>
<?php
pie();
