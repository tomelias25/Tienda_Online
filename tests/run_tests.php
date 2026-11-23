<?php
/**
 * tests/run_tests.php - Pruebas unitarias y de integración (sin dependencias externas).
 *
 * Uso (con MySQL de XAMPP encendido):
 *     C:\xampp\php\php.exe tests\run_tests.php
 *
 * Crea/recrea una base de datos aparte llamada `tienda_online_test`
 * a partir de database/tienda_online.sql, por lo que NO toca la base real.
 */
require_once __DIR__ . '/../config.php';          // constantes y helpers (e, csrf)
require_once __DIR__ . '/../validaciones.php';
require_once __DIR__ . '/../autenticacion.php';

const BD_PRUEBA = 'tienda_online_test';
const CLAVE_DEMO = 'Tienda2026*';

// ---------- mini framework ----------
$GLOBALS['res'] = ['ok' => 0, 'fallo' => 0, 'lineas' => []];

function prueba(string $id, string $desc, callable $fn): void
{
    try {
        $fn();
        $GLOBALS['res']['ok']++;
        $GLOBALS['res']['lineas'][] = "[OK]    $id  $desc";
    } catch (Throwable $t) {
        $GLOBALS['res']['fallo']++;
        $GLOBALS['res']['lineas'][] = "[FALLO] $id  $desc\n          -> " . $t->getMessage();
    }
}
function afirmar(bool $cond, string $msg = 'La condición es falsa'): void { if (!$cond) throw new Exception($msg); }
function igual($esperado, $real, string $msg = ''): void
{
    if ($esperado !== $real) throw new Exception(($msg ? "$msg: " : '') . 'esperado ' . var_export($esperado, true) . ' y se obtuvo ' . var_export($real, true));
}
/** Verifica que la función lance PDOException (violación de restricción). */
function debe_fallar(callable $fn, string $msg): void
{
    try { $fn(); } catch (PDOException $e) { return; }
    throw new Exception($msg);
}

// ---------- base de datos de prueba ----------
function cargar_bd(): PDO
{
    static $admin = null;
    if ($admin === null) {
        $admin = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    $sql = file_get_contents(__DIR__ . '/../database/tienda_online.sql');
    $sql = str_replace('`tienda_online`', '`' . BD_PRUEBA . '`', $sql);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);                 // quitar comentarios
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) as $stmt) {
        if (trim($stmt) !== '') $admin->exec($stmt);
    }
    // Conexión nueva en modo estricto, como en producción.
    return new PDO('mysql:host=' . DB_HOST . ';dbname=' . BD_PRUEBA . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode='STRICT_ALL_TABLES,NO_AUTO_VALUE_ON_ZERO'",
    ]);
}
function esc(PDO $p, string $sql, array $a = []) { $s = $p->prepare($sql); $s->execute($a); return $s; }
function uno(PDO $p, string $sql, array $a = []) { return esc($p, $sql, $a)->fetchColumn(); }

try {
    $pdo = cargar_bd();
} catch (Throwable $t) {
    fwrite(STDERR, "No se pudo preparar la base de prueba: " . $t->getMessage() . "\nVerifique que MySQL esté encendido y las credenciales de config.php.\n");
    exit(2);
}

// =====================================================================
// GRUPO 1 - PRUEBAS UNITARIAS (sin base de datos): validaciones.php y config.php
// =====================================================================
prueba('U01', 'email_valido acepta un correo correcto', fn() => afirmar(email_valido('ana@mail.com')));
prueba('U02', 'email_valido rechaza un correo sin @', fn() => afirmar(!email_valido('ana.mail.com')));
prueba('U03', 'email_valido rechaza cadena vacía', fn() => afirmar(!email_valido('')));
prueba('U04', 'email_valido rechaza más de 80 caracteres', fn() => afirmar(!email_valido(str_repeat('a', 75) . '@mail.com')));
prueba('U05', 'contrasena_valida acepta "Clave2026x"', fn() => afirmar(contrasena_valida('Clave2026x')));
prueba('U06', 'contrasena_valida rechaza menos de 8 caracteres', fn() => afirmar(!contrasena_valida('Ab1')));
prueba('U07', 'contrasena_valida rechaza contraseña sin números', fn() => afirmar(!contrasena_valida('SoloLetras')));
prueba('U08', 'contrasena_valida rechaza contraseña sin letras', fn() => afirmar(!contrasena_valida('12345678')));
$ok = ['nombre' => 'Ana', 'email' => 'ana@mail.com', 'ciudad' => 'Cúcuta', 'password' => 'Clave2026x', 'password2' => 'Clave2026x'];
prueba('U09', 'validar_registro: datos correctos no generan errores', fn() => igual([], validar_registro($ok)));
prueba('U10', 'validar_registro: nombre vacío genera error', fn() => igual(1, count(validar_registro(['nombre' => ''] + $ok))));
prueba('U11', 'validar_registro: nombre de 51 caracteres genera error', fn() => igual(1, count(validar_registro(['nombre' => str_repeat('a', 51)] + $ok))));
prueba('U12', 'validar_registro: contraseñas distintas generan error', fn() => igual(1, count(validar_registro(['password2' => 'Otra2026xx'] + $ok))));
prueba('U13', 'validar_registro: ciudad de 51 caracteres genera error', fn() => igual(1, count(validar_registro(['ciudad' => str_repeat('c', 51)] + $ok))));
prueba('U14', 'e() escapa HTML para prevenir XSS', fn() => igual('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>')));
prueba('U15', 'largo() cuenta tildes y ñ como un carácter', fn() => igual(5, largo('Sofía')));
$_SESSION['csrf'] = 'token-de-prueba';
prueba('U16', 'csrf_ok acepta el token correcto', fn() => afirmar(csrf_ok('token-de-prueba')));
prueba('U17', 'csrf_ok rechaza un token distinto', fn() => afirmar(!csrf_ok('otro-token')));
prueba('U18', 'csrf_ok rechaza token nulo', fn() => afirmar(!csrf_ok(null)));

// =====================================================================
// GRUPO 2 - INTEGRACIÓN CON LA BASE DE DATOS (integridad del modelo)
// =====================================================================
$pdo = cargar_bd();
prueba('B01', 'Existen las 5 tablas del modelo', function () use ($pdo) {
    igual(5, (int)uno($pdo, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=? AND table_name IN ('categorias','productos','usuarios','pedidos','detalle_pedidos')", [BD_PRUEBA]));
});
prueba('B02', 'Ningún usuario tiene la contraseña vacía', fn() => igual(0, (int)uno($pdo, "SELECT COUNT(*) FROM usuarios WHERE password=''")));
prueba('B03', 'Los 10 usuarios guardan un hash bcrypt verificable', function () use ($pdo) {
    $n = 0;
    foreach (esc($pdo, 'SELECT password FROM usuarios')->fetchAll() as $f) {
        afirmar(str_starts_with($f['password'], '$2y$'), 'No es bcrypt');
        afirmar(password_verify(CLAVE_DEMO, $f['password']), 'El hash no corresponde a la clave de demostración');
        $n++;
    }
    igual(10, $n);
});
prueba('B04', 'El correo es UNIQUE: no permite duplicados', fn() => debe_fallar(
    fn() => esc($pdo, "INSERT INTO usuarios (nombre,email,password) VALUES ('Dup','laura.gomez@mail.com','x')"), 'Permitió un correo duplicado'));
prueba('B05', 'FK: un pedido no puede tener un usuario inexistente', fn() => debe_fallar(
    fn() => esc($pdo, "INSERT INTO pedidos (fecha_pedido,id_user) VALUES ('2026-10-01',9999)"), 'Aceptó un id_user inexistente'));
prueba('B06', 'FK: un detalle no puede apuntar a un producto inexistente', fn() => debe_fallar(
    fn() => esc($pdo, 'INSERT INTO detalle_pedidos (id_pedido,id_producto,cantidad,precio_unitario) VALUES (1,9999,1,100)'), 'Aceptó un id_producto inexistente'));
prueba('B07', 'CHECK: el stock no puede ser negativo', fn() => debe_fallar(
    fn() => esc($pdo, 'UPDATE productos SET stock=-1 WHERE id_producto=1'), 'Aceptó stock negativo'));
prueba('B08', 'CHECK: la cantidad de un detalle debe ser mayor que 0', fn() => debe_fallar(
    fn() => esc($pdo, 'INSERT INTO detalle_pedidos (id_pedido,id_producto,cantidad,precio_unitario) VALUES (1,1,0,100)'), 'Aceptó cantidad 0'));
prueba('B09', 'NOT NULL: un pedido exige id_user', fn() => debe_fallar(
    fn() => esc($pdo, "INSERT INTO pedidos (fecha_pedido,id_user) VALUES ('2026-10-01',NULL)"), 'Aceptó id_user NULL'));
prueba('B10', 'ENUM: el rol solo admite cliente o administrador', fn() => debe_fallar(
    fn() => esc($pdo, "INSERT INTO usuarios (nombre,email,password,rol) VALUES ('X','x@mail.com','h','superadmin')"), 'Aceptó un rol inválido'));
prueba('B11', 'Integridad contable: total del pedido = suma de cantidad x precio_unitario', function () use ($pdo) {
    igual(0, (int)uno($pdo, 'SELECT COUNT(*) FROM pedidos p JOIN (SELECT id_pedido, SUM(cantidad*precio_unitario) s FROM detalle_pedidos GROUP BY id_pedido) d USING (id_pedido) WHERE p.total <> d.s'));
});
prueba('B12', 'No se puede borrar un producto que ya tiene pedidos (RESTRICT)', fn() => debe_fallar(
    fn() => esc($pdo, 'DELETE FROM productos WHERE id_producto=1'), 'Permitió borrar un producto vendido'));
prueba('B13', 'Consulta de ventas por categoría suma $927.000 en total', function () use ($pdo) {
    $t = uno($pdo, 'SELECT SUM(d.cantidad*d.precio_unitario) FROM detalle_pedidos d JOIN productos p USING (id_producto) JOIN categorias c USING (id_categoria)');
    igual(927000.0, (float)$t);
});
prueba('B14', 'Hay 4 clientes en Cúcuta (40 % de los 10 usuarios)', fn() => igual(4, (int)uno($pdo, "SELECT COUNT(*) FROM usuarios WHERE ciudad='Cúcuta'")));

// =====================================================================
// GRUPO 3 - AUTENTICACIÓN (intentar_login)
// =====================================================================
$pdo = cargar_bd();
$rst = fn() => esc($pdo, 'UPDATE usuarios SET intentos_fallidos=0, bloqueado_hasta=NULL');
prueba('A01', 'Login correcto del administrador devuelve rol administrador', function () use ($pdo) {
    $r = intentar_login($pdo, 'laura.gomez@mail.com', CLAVE_DEMO);
    afirmar($r['ok']); igual('administrador', $r['usuario']['rol']);
});
prueba('A02', 'Login correcto de un cliente devuelve rol cliente', function () use ($pdo) {
    $r = intentar_login($pdo, 'ana.rodriguez@mail.com', CLAVE_DEMO);
    afirmar($r['ok']); igual('cliente', $r['usuario']['rol']);
});
prueba('A03', 'Contraseña incorrecta: mensaje genérico', function () use ($pdo) {
    $r = intentar_login($pdo, 'juan.perez@mail.com', 'mala');
    afirmar(!$r['ok']); igual('Correo o contraseña incorrectos.', $r['mensaje']);
});
prueba('A04', 'Correo inexistente: el MISMO mensaje genérico (no revela si existe)', function () use ($pdo) {
    $r = intentar_login($pdo, 'nadie@mail.com', 'mala');
    afirmar(!$r['ok']); igual('Correo o contraseña incorrectos.', $r['mensaje']);
});
prueba('A05', 'Entrada vacía o con formato de correo inválido es rechazada', function () use ($pdo) {
    afirmar(!intentar_login($pdo, '', CLAVE_DEMO)['ok']);
    afirmar(!intentar_login($pdo, 'no-es-correo', CLAVE_DEMO)['ok']);
    afirmar(!intentar_login($pdo, 'ana.rodriguez@mail.com', '')['ok']);
});
prueba('A06', "Inyección SQL (' OR '1'='1) no autentica", function () use ($pdo) {
    afirmar(!intentar_login($pdo, "' OR '1'='1", "x' OR '1'='1")['ok']);
    afirmar(!intentar_login($pdo, "laura.gomez@mail.com' --", 'x')['ok']);
});
prueba('A07', 'Cada fallo incrementa el contador de intentos', function () use ($pdo, $rst) {
    $rst(); intentar_login($pdo, 'carlos.ruiz@mail.com', 'mala'); intentar_login($pdo, 'carlos.ruiz@mail.com', 'mala');
    igual(2, (int)uno($pdo, "SELECT intentos_fallidos FROM usuarios WHERE email='carlos.ruiz@mail.com'"));
});
prueba('A08', 'Un login correcto reinicia el contador de intentos', function () use ($pdo) {
    afirmar(intentar_login($pdo, 'carlos.ruiz@mail.com', CLAVE_DEMO)['ok']);
    igual(0, (int)uno($pdo, "SELECT intentos_fallidos FROM usuarios WHERE email='carlos.ruiz@mail.com'"));
});
prueba('A09', 'Tras ' . MAX_INTENTOS . ' fallos la cuenta se bloquea', function () use ($pdo, $rst) {
    $rst(); for ($i = 0; $i < MAX_INTENTOS; $i++) intentar_login($pdo, 'diego.martinez@mail.com', 'mala');
    afirmar(uno($pdo, "SELECT bloqueado_hasta FROM usuarios WHERE email='diego.martinez@mail.com'") !== null, 'No quedó bloqueada');
});
prueba('A10', 'Una cuenta bloqueada rechaza incluso la contraseña correcta', function () use ($pdo) {
    $r = intentar_login($pdo, 'diego.martinez@mail.com', CLAVE_DEMO);
    afirmar(!$r['ok']); afirmar(str_contains($r['mensaje'], 'bloqueada'), 'Mensaje inesperado: ' . $r['mensaje']);
});
prueba('A11', 'Al vencer el bloqueo la cuenta vuelve a permitir el ingreso', function () use ($pdo) {
    esc($pdo, "UPDATE usuarios SET bloqueado_hasta = NOW() - INTERVAL 1 MINUTE WHERE email='diego.martinez@mail.com'");
    afirmar(intentar_login($pdo, 'diego.martinez@mail.com', CLAVE_DEMO)['ok']);
});
prueba('A12', 'Una cuenta con contraseña vacía en la BD no entra con contraseña vacía', function () use ($pdo) {
    esc($pdo, "UPDATE usuarios SET password='' WHERE email='valentina.diaz@mail.com'");
    afirmar(!intentar_login($pdo, 'valentina.diaz@mail.com', '')['ok']);
    afirmar(!intentar_login($pdo, 'valentina.diaz@mail.com', CLAVE_DEMO)['ok']);
});

// =====================================================================
// GRUPO 4 - REGISTRO (registrar_usuario)
// =====================================================================
$pdo = cargar_bd();
$nuevo = ['nombre' => 'Prueba Nueva', 'email' => 'nuevo@mail.com', 'ciudad' => 'Cúcuta', 'password' => 'Clave2026x', 'password2' => 'Clave2026x'];
prueba('R01', 'Registro válido crea un usuario con rol cliente', function () use ($pdo, $nuevo) {
    $r = registrar_usuario($pdo, $nuevo);
    afirmar($r['ok'], implode(' ', $r['errores']));
    igual('cliente', uno($pdo, "SELECT rol FROM usuarios WHERE email='nuevo@mail.com'"));
});
prueba('R02', 'La contraseña se guarda cifrada (nunca en texto plano)', function () use ($pdo) {
    $h = uno($pdo, "SELECT password FROM usuarios WHERE email='nuevo@mail.com'");
    afirmar($h !== 'Clave2026x'); afirmar(str_starts_with($h, '$2y$')); afirmar(password_verify('Clave2026x', $h));
});
prueba('R03', 'El usuario recién registrado puede iniciar sesión', fn() => afirmar(intentar_login($pdo, 'nuevo@mail.com', 'Clave2026x')['ok']));
prueba('R04', 'Registro con correo duplicado es rechazado', function () use ($pdo, $nuevo) {
    $r = registrar_usuario($pdo, $nuevo);
    afirmar(!$r['ok']); igual('Ese correo ya está registrado.', $r['errores'][0]);
});
prueba('R05', 'Registro con datos inválidos no inserta nada', function () use ($pdo) {
    $antes = (int)uno($pdo, 'SELECT COUNT(*) FROM usuarios');
    $r = registrar_usuario($pdo, ['nombre' => '', 'email' => 'mal', 'ciudad' => '', 'password' => 'a', 'password2' => 'b']);
    afirmar(!$r['ok']); igual($antes, (int)uno($pdo, 'SELECT COUNT(*) FROM usuarios'));
});

// ---------- informe ----------
echo "\n=== Resultados de las pruebas ===\n";
foreach ($GLOBALS['res']['lineas'] as $l) echo $l . "\n";
$tot = $GLOBALS['res']['ok'] + $GLOBALS['res']['fallo'];
$pct = $tot ? round(100 * $GLOBALS['res']['ok'] / $tot, 1) : 0;
echo "\nTotal: $tot | Aprobadas: {$GLOBALS['res']['ok']} | Falladas: {$GLOBALS['res']['fallo']} | Aprobación: {$pct} %\n";
exit($GLOBALS['res']['fallo'] > 0 ? 1 : 0);
