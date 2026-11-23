<?php
/**
 * validaciones.php - Reglas de validación puras (sin base de datos ni sesión).
 * Se prueban con tests/run_tests.php (pruebas unitarias).
 */

/** Longitud en caracteres (cuenta bien las tildes y la ñ). */
function largo(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen(utf8_decode($s));
}

function email_valido(string $email): bool
{
    return $email !== '' && largo($email) <= 80 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Mínimo 8 caracteres, con al menos una letra y un número. */
function contrasena_valida(string $pass): bool
{
    return strlen($pass) >= 8 && preg_match('/[A-Za-z]/', $pass) === 1 && preg_match('/\d/', $pass) === 1;
}

/**
 * Valida los datos del formulario de registro.
 * @param array $in claves: nombre, email, ciudad, password, password2
 * @return string[] lista de mensajes de error (vacía si todo es válido)
 */
function validar_registro(array $in): array
{
    $errores = [];
    $nombre = trim((string)($in['nombre'] ?? ''));
    $email  = trim((string)($in['email'] ?? ''));
    $ciudad = trim((string)($in['ciudad'] ?? ''));
    $p1 = (string)($in['password'] ?? '');
    $p2 = (string)($in['password2'] ?? '');

    if ($nombre === '' || largo($nombre) > 50) $errores[] = 'Ingrese un nombre (máximo 50 caracteres).';
    if (!email_valido($email))                 $errores[] = 'Ingrese un correo válido.';
    if (largo($ciudad) > 50)                   $errores[] = 'La ciudad es demasiado larga.';
    if (!contrasena_valida($p1))               $errores[] = 'La contraseña debe tener mínimo 8 caracteres, con letras y números.';
    if ($p1 !== $p2)                           $errores[] = 'Las contraseñas no coinciden.';
    return $errores;
}
