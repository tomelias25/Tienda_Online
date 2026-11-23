<?php
require __DIR__ . '/config.php';
require __DIR__ . '/autenticacion.php';

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $errores[] = 'Sesión expirada. Recargue la página.';
    } else {
        $r = registrar_usuario(db(), $_POST);
        if ($r['ok']) { header('Location: login.php?registrado=1'); exit; }
        $errores = $r['errores'];
    }
}
cabecera('Registro'); ?>
<div class="card login">
  <h1>Crear cuenta</h1>
  <?php foreach ($errores as $m): ?><div class="msg err" role="alert"><?= e($m) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="nombre">Nombre</label><input id="nombre" name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>">
    <label for="email">Correo electrónico</label><input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
    <label for="ciudad">Ciudad</label><input id="ciudad" name="ciudad" value="<?= e($_POST['ciudad'] ?? '') ?>">
    <label for="password">Contraseña (mín. 8, letras y números)</label><input id="password" name="password" type="password" required>
    <label for="password2">Repetir contraseña</label><input id="password2" name="password2" type="password" required>
    <button type="submit">Registrarme</button>
  </form>
  <p class="mut" style="margin-top:18px"><a href="login.php">Volver a iniciar sesión</a></p>
</div>
<?php pie();
