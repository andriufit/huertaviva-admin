<?php
declare(strict_types=1);

require __DIR__ . '/../src/web.php';

exigir_login();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $accion = (string) ($_POST['accion'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01, 'max_range' => 999999]]);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($accion === 'crear') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $unidad = trim((string) ($_POST['unidad'] ?? ''));

        if ($nombre === '' || mb_strlen($nombre) > 100 || mb_strlen($descripcion) > 500
            || $unidad === '' || mb_strlen($unidad) > 20 || $precio === false || $stock === false) {
            $error = 'Revisa los datos del producto.';
        } else {
            $consulta = db()->prepare('INSERT INTO productos (nombre, descripcion, precio, unidad, stock) VALUES (?, ?, ?, ?, ?)');
            $consulta->execute([$nombre, $descripcion, $precio, $unidad, $stock]);
            redirigir('productos.php');
        }
    } elseif ($accion === 'actualizar' && $id !== false && $precio !== false && $stock !== false) {
        $consulta = db()->prepare('UPDATE productos SET precio = ?, stock = ? WHERE id = ?');
        $consulta->execute([$precio, $stock, $id]);
        redirigir('productos.php');
    } elseif ($accion === 'alternar' && $id !== false) {
        $consulta = db()->prepare('UPDATE productos SET activo = 1 - activo WHERE id = ?');
        $consulta->execute([$id]);
        redirigir('productos.php');
    } else {
        $error = 'Revisa los datos del producto.';
    }
}

$productos = db()->query('SELECT id, nombre, descripcion, precio, unidad, stock, activo FROM productos ORDER BY nombre')->fetchAll();

cabecera('Productos');
?>
<?php if ($error !== null): ?>
  <p class="aviso aviso-error"><?= e($error) ?></p>
<?php endif; ?>

<table>
  <thead>
    <tr><th>Producto</th><th>Precio (€)</th><th>Stock</th><th>Estado</th><th></th></tr>
  </thead>
  <tbody>
    <?php foreach ($productos as $producto): ?>
      <tr class="<?= $producto['activo'] ? '' : 'inactivo' ?>">
        <td><?= e($producto['nombre']) ?><br><small><?= e($producto['descripcion']) ?></small></td>
        <td colspan="2">
          <form method="post" class="en-linea">
            <?= campo_csrf() ?>
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id" value="<?= e($producto['id']) ?>">
            <input type="number" name="precio" step="0.01" min="0.01" value="<?= e($producto['precio']) ?>" class="corto"> / <?= e($producto['unidad']) ?>
            <input type="number" name="stock" min="0" value="<?= e($producto['stock']) ?>" class="corto">
            <button type="submit">Guardar</button>
          </form>
        </td>
        <td><?= $producto['activo'] ? 'Activo' : 'Oculto' ?></td>
        <td>
          <form method="post" class="en-linea">
            <?= campo_csrf() ?>
            <input type="hidden" name="accion" value="alternar">
            <input type="hidden" name="id" value="<?= e($producto['id']) ?>">
            <button type="submit"><?= $producto['activo'] ? 'Ocultar' : 'Activar' ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2>Nuevo producto</h2>
<form method="post" class="formulario">
  <?= campo_csrf() ?>
  <input type="hidden" name="accion" value="crear">
  <label>Nombre <input type="text" name="nombre" maxlength="100" required></label>
  <label>Descripción <textarea name="descripcion" maxlength="500" rows="2"></textarea></label>
  <label>Precio (€) <input type="number" name="precio" step="0.01" min="0.01" required></label>
  <label>Unidad <input type="text" name="unidad" maxlength="20" value="kg" required></label>
  <label>Stock <input type="number" name="stock" min="0" value="0" required></label>
  <button type="submit">Crear producto</button>
</form>
<?php
pie();
