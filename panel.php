<?php
require __DIR__ . '/config.php';
requiere_login();
$esAdmin = ($_SESSION['rol'] ?? '') === 'administrador';

$prod = db()->query('SELECT p.nombre_producto, c.nombre_categoria, p.precio, p.stock
                     FROM productos p JOIN categorias c USING (id_categoria)
                     WHERE p.activo = 1 ORDER BY c.nombre_categoria, p.nombre_producto')->fetchAll();

if ($esAdmin) {
    $ped = db()->query('SELECT p.id_pedido, p.fecha_pedido, u.nombre AS cliente, p.estado, p.total
                        FROM pedidos p JOIN usuarios u USING (id_user) ORDER BY p.id_pedido DESC')->fetchAll();
} else {
    $st = db()->prepare('SELECT id_pedido, fecha_pedido, estado, total FROM pedidos WHERE id_user = ? ORDER BY id_pedido DESC');
    $st->execute([$_SESSION['id_user']]);
    $ped = $st->fetchAll();
}
$cop = fn($n) => '$' . number_format((float)$n, 0, ',', '.');
cabecera('Panel'); ?>
<div class="card top">
  <div><h1>Hola, <?= e($_SESSION['nombre']) ?></h1>
  <span class="mut"><?= e(NOMBRE_EMPRESA) ?> · Rol: <?= e($_SESSION['rol']) ?></span></div>
  <a class="btn sec" href="logout.php">Cerrar sesión</a>
</div>
<div class="card"><h2>Catálogo</h2><div class="scroll"><table>
<tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr>
<?php foreach ($prod as $p): ?>
<tr><td><?= e($p['nombre_producto']) ?></td><td><?= e($p['nombre_categoria']) ?></td><td><?= $cop($p['precio']) ?></td><td><?= (int)$p['stock'] ?></td></tr>
<?php endforeach; ?></table></div></div>
<div class="card"><h2><?= $esAdmin ? 'Todos los pedidos' : 'Mis pedidos' ?></h2><div class="scroll"><table>
<tr><th>#</th><th>Fecha</th><?php if ($esAdmin): ?><th>Cliente</th><?php endif; ?><th>Estado</th><th>Total</th></tr>
<?php foreach ($ped as $o): ?>
<tr><td><?= (int)$o['id_pedido'] ?></td><td><?= e($o['fecha_pedido']) ?></td><?php if ($esAdmin): ?><td><?= e($o['cliente']) ?></td><?php endif; ?><td><?= e($o['estado']) ?></td><td><?= $cop($o['total']) ?></td></tr>
<?php endforeach; if (!$ped): ?><tr><td colspan="5" class="mut">Aún no tiene pedidos.</td></tr><?php endif; ?></table></div></div>
<?php pie();
