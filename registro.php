<?php
require __DIR__ . '/config.php';
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $email  = trim((string)($_POST['email'] ?? ''));
    $ciudad = trim((string)($_POST['ciudad'] ?? ''));
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    if (!csrf_ok($_POST['csrf'] ?? null)) $errores[] = 'Sesión expirada. Recargue la página.';
    if ($nombre === '' || largo($nombre) > 50) $errores[] = 'Ingrese un nombre (máximo 50 caracteres).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || largo($email) > 80) $errores[] = 'Ingrese un correo válido.';
    if (largo($ciudad) > 50) $errores[] = 'La ciudad es demasiado larga.';
    if (strlen($p1) < 8 || !preg_match('/[A-Za-z]/', $p1) || !preg_match('/\d/', $p1)) $errores[] = 'La contraseña debe tener mínimo 8 caracteres, con letras y números.';
    if ($p1 !== $p2) $errores[] = 'Las contraseñas no coinciden.';
    if (!$errores) {
        try {
            db()->prepare('INSERT INTO usuarios (nombre,email,password,ciudad,rol) VALUES (?,?,?,?, "cliente")')
                ->execute([$nombre, $email, password_hash($p1, PASSWORD_BCRYPT), $ciudad ?: null]);
            header('Location: login.php?registrado=1'); exit;
        } catch (PDOException $ex) {
            $errores[] = ($ex->getCode() === '23000') ? 'Ese correo ya está registrado.' : 'No se pudo crear la cuenta.';
        }
    }
}
cabecera('Registro'); ?>
<div class="card login">
  <h1>Crear cuenta</h1>
  <?php foreach ($errores as $m): ?><div class="msg err"><?= e($m) ?></div><?php endforeach; ?>
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
