<?php
declare(strict_types=1);

require __DIR__ . '/../src/web.php';

exigir_login();

const ESTADOS = ['pendiente', 'preparado', 'entregado', 'cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $estado = (string) ($_POST['estado'] ?? '');

    if ($id !== false && in_array($estado, ESTADOS, true)) {
        $consulta = db()->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
        $consulta->execute([$estado, $id]);
    }
    redirigir('index.php');
}

$pedidos = db()->query(
    'SELECT p.id, p.cantidad, p.nombre_cliente, p.email_cliente, p.comentario, p.estado, p.creado_en,
            pr.nombre AS producto, pr.unidad
       FROM pedidos p
       JOIN productos pr ON pr.id = p.producto_id
   ORDER BY p.creado_en DESC'
)->fetchAll();

cabecera('Pedidos');
?>
<?php if (count($pedidos) === 0): ?>
  <p>Todavía no hay pedidos.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>N.º</th><th>Fecha</th><th>Cliente</th><th>Producto</th><th>Cantidad</th><th>Comentario</th><th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pedidos as $pedido): ?>
        <tr>
          <td><?= e($pedido['id']) ?></td>
          <td><?= e($pedido['creado_en']) ?></td>
          <td><?= e($pedido['nombre_cliente']) ?><br><small><?= e($pedido['email_cliente']) ?></small></td>
          <td><?= e($pedido['producto']) ?></td>
          <td><?= e($pedido['cantidad']) ?> <?= e($pedido['unidad']) ?></td>
          <td><?= e($pedido['comentario']) ?></td>
          <td>
            <form method="post" class="en-linea">
              <?= campo_csrf() ?>
              <input type="hidden" name="id" value="<?= e($pedido['id']) ?>">
              <select name="estado">
                <?php foreach (ESTADOS as $estado): ?>
                  <option value="<?= e($estado) ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= e($estado) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit">Guardar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php
pie();
