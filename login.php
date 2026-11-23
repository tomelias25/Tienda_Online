<?php
require __DIR__ . '/config.php';
require __DIR__ . '/autenticacion.php';

if (!empty($_SESSION['id_user'])) { header('Location: panel.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $error = 'Sesión expirada. Recargue la página e intente de nuevo.';
    } else {
        $r = intentar_login(db(), (string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''));
        if ($r['ok']) {
            session_regenerate_id(true);               // evita fijación de sesión
            $_SESSION['id_user'] = $r['usuario']['id_user'];
            $_SESSION['nombre']  = $r['usuario']['nombre'];
            $_SESSION['rol']     = $r['usuario']['rol'];
            $_SESSION['csrf']    = bin2hex(random_bytes(32));
            header('Location: panel.php'); exit;
        }
        $error = $r['mensaje'];
    }
}
cabecera('Iniciar sesión'); ?>
<div class="card login">
  <h1><?= e(NOMBRE_EMPRESA) ?> · Tienda online</h1>
  <p class="mut">Inicie sesión para continuar</p>
  <?php if ($error): ?><div class="msg err" role="alert"><?= e($error) ?></div><?php endif; ?>
  <?php if (isset($_GET['registrado'])): ?><div class="msg ok">Cuenta creada. Ya puede iniciar sesión.</div><?php endif; ?>
  <?php if (isset($_GET['salio'])): ?><div class="msg ok">Sesión cerrada correctamente.</div><?php endif; ?>
  <form method="post" autocomplete="on" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="email">Correo electrónico</label>
    <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" required>
    <button type="submit">Ingresar</button>
  </form>
  <p class="mut" style="margin-top:18px">¿No tiene cuenta? <a href="registro.php">Regístrese</a></p>
</div>
<?php pie();
