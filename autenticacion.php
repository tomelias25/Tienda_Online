<?php
/**
 * autenticacion.php - Lógica de inicio de sesión y registro (capa de negocio).
 * Recibe la conexión PDO por parámetro para poder probarse con una base de datos de prueba.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/validaciones.php';

/**
 * Intenta autenticar a un usuario.
 * @return array ['ok'=>bool, 'mensaje'=>string, 'usuario'=>array|null]
 */
function intentar_login(PDO $pdo, string $email, string $pass): array
{
    $email = trim($email);
    if ($email === '' || $pass === '' || !email_valido($email)) {
        return ['ok' => false, 'mensaje' => 'Ingrese un correo válido y su contraseña.', 'usuario' => null];
    }

    // La comparación de fechas se hace en SQL para no depender de la zona horaria de PHP.
    $st = $pdo->prepare('SELECT id_user, nombre, email, password, rol, intentos_fallidos,
                                (bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW()) AS bloqueado,
                                GREATEST(TIMESTAMPDIFF(SECOND, NOW(), bloqueado_hasta), 0) AS segundos
                         FROM usuarios WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && (int)$u['bloqueado'] === 1) {
        $min = max(1, (int)ceil(((int)$u['segundos']) / 60));
        return ['ok' => false, 'mensaje' => "Cuenta bloqueada temporalmente. Intente de nuevo en $min minuto(s).", 'usuario' => null];
    }

    if ($u && $u['password'] !== '' && password_verify($pass, $u['password'])) {
        if (password_needs_rehash($u['password'], PASSWORD_BCRYPT)) {
            $pdo->prepare('UPDATE usuarios SET password=? WHERE id_user=?')
                ->execute([password_hash($pass, PASSWORD_BCRYPT), $u['id_user']]);
        }
        $pdo->prepare('UPDATE usuarios SET intentos_fallidos=0, bloqueado_hasta=NULL WHERE id_user=?')
            ->execute([$u['id_user']]);
        return ['ok' => true, 'mensaje' => '', 'usuario' => [
            'id_user' => (int)$u['id_user'], 'nombre' => $u['nombre'], 'email' => $u['email'], 'rol' => $u['rol'],
        ]];
    }

    if ($u) {
        $n = (int)$u['intentos_fallidos'] + 1;
        if ($n >= MAX_INTENTOS) {
            $pdo->prepare('UPDATE usuarios SET intentos_fallidos=0, bloqueado_hasta=DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id_user=?')
                ->execute([MINUTOS_BLOQUEO, $u['id_user']]);
        } else {
            $pdo->prepare('UPDATE usuarios SET intentos_fallidos=? WHERE id_user=?')->execute([$n, $u['id_user']]);
        }
    }
    // Mismo mensaje exista o no el correo (evita enumerar usuarios).
    return ['ok' => false, 'mensaje' => 'Correo o contraseña incorrectos.', 'usuario' => null];
}

/**
 * Registra un cliente nuevo con la contraseña cifrada (bcrypt).
 * @return array ['ok'=>bool, 'errores'=>string[]]
 */
function registrar_usuario(PDO $pdo, array $in): array
{
    $errores = validar_registro($in);
    if ($errores) return ['ok' => false, 'errores' => $errores];

    $ciudad = trim((string)($in['ciudad'] ?? ''));
    try {
        $pdo->prepare('INSERT INTO usuarios (nombre,email,password,ciudad,rol) VALUES (?,?,?,?, "cliente")')
            ->execute([trim($in['nombre']), trim($in['email']), password_hash((string)$in['password'], PASSWORD_BCRYPT), $ciudad !== '' ? $ciudad : null]);
    } catch (PDOException $ex) {
        return ['ok' => false, 'errores' => [($ex->getCode() === '23000') ? 'Ese correo ya está registrado.' : 'No se pudo crear la cuenta.']];
    }
    return ['ok' => true, 'errores' => []];
}
