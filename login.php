<?php
require __DIR__ . '/config.php';

if (!empty($_SESSION['id_user'])) { header('Location: panel.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $msgGenerico = 'Correo o contraseña incorrectos.';

    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $error = 'Sesión expirada. Recargue la página e intente de nuevo.';
    } elseif ($email === '' || $pass === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingrese un correo válido y su contraseña.';
    } else {
        $st = db()->prepare('SELECT id_user, nombre, email, password, rol, intentos_fallidos, bloqueado_hasta
                             FROM usuarios WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();

        if ($u && $u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time()) {
            $min = (int)ceil((strtotime($u['bloqueado_hasta']) - time()) / 60);
            $error = "Cuenta bloqueada temporalmente. Intente de nuevo en $min minuto(s).";
        } elseif ($u && $u['password'] !== '' && password_verify($pass, $u['password'])) {
            // éxito: reiniciar contador, renovar sesión y rehash si es necesario
            if (password_needs_rehash($u['password'], PASSWORD_BCRYPT)) {
                db()->prepare('UPDATE usuarios SET password=? WHERE id_user=?')
                    ->execute([password_hash($pass, PASSWORD_BCRYPT), $u['id_user']]);
            }
            db()->prepare('UPDATE usuarios SET intentos_fallidos=0, bloqueado_hasta=NULL WHERE id_user=?')
                ->execute([$u['id_user']]);
            session_regenerate_id(true);
            $_SESSION['id_user'] = (int)$u['id_user'];
            $_SESSION['nombre']  = $u['nombre'];
            $_SESSION['rol']     = $u['rol'];
            $_SESSION['csrf']    = bin2hex(random_bytes(32));
            header('Location: panel.php'); exit;
        } else {
            if ($u) {
                $n = (int)$u['intentos_fallidos'] + 1;
                if ($n >= MAX_INTENTOS) {
                    db()->prepare('UPDATE usuarios SET intentos_fallidos=0, bloqueado_hasta=DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id_user=?')
                        ->execute([MINUTOS_BLOQUEO, $u['id_user']]);
                } else {
                    db()->prepare('UPDATE usuarios SET intentos_fallidos=? WHERE id_user=?')->execute([$n, $u['id_user']]);
                }
            } else {
                password_verify($pass, '$2y$10$usesomesillystringforsaltoOtpRzKX1nQ7mL8vPq0yWcS2dT4eUa'); // tiempo constante
            }
            $error = $msgGenerico;
        }
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
