<?php
const NOMBRE_EMPRESA = 'Tiendamalboro';

const DB_HOST = '127.0.0.1';
const DB_NAME = 'tienda_online';
const DB_USER = 'root';
const DB_PASS = '';

const MAX_INTENTOS   = 5;  
const MINUTOS_BLOQUEO = 5;

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}

function largo(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen(utf8_decode($s)); }
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_ok(?string $t): bool {
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}
function requiere_login(): void {
    if (empty($_SESSION['id_user'])) { header('Location: login.php'); exit; }
}
function cabecera(string $titulo): void { ?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> | <?= e(NOMBRE_EMPRESA) ?></title>
<style>
:root{--bg:#f4f6fb;--card:#fff;--tx:#1d2433;--mut:#667085;--pri:#1f4fd8;--bd:#dfe3ec;--err:#b42318;--ok:#067647}
@media (prefers-color-scheme:dark){:root{--bg:#10131a;--card:#1a1f2b;--tx:#e7eaf2;--mut:#98a2b3;--pri:#6c8cff;--bd:#2c3445;--err:#ff8a80;--ok:#6ce9a6}}
*{box-sizing:border-box}body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--tx)}
.wrap{max-width:960px;margin:0 auto;padding:24px 16px}
.card{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:24px;margin-bottom:20px}
.login{max-width:400px;margin:8vh auto}
h1{font-size:1.4rem;margin:0 0 4px}h2{font-size:1.1rem;margin:0 0 12px}.mut{color:var(--mut);font-size:.9rem}
label{display:block;font-size:.9rem;margin:14px 0 6px}
input{width:100%;padding:10px 12px;border:1px solid var(--bd);border-radius:8px;background:var(--bg);color:var(--tx);font-size:1rem}
button,.btn{display:inline-block;margin-top:18px;padding:10px 18px;border:0;border-radius:8px;background:var(--pri);color:#fff;font-size:1rem;cursor:pointer;text-decoration:none}
.btn.sec{background:transparent;color:var(--pri);border:1px solid var(--pri);margin-top:0}
.msg{padding:10px 12px;border-radius:8px;margin:14px 0;font-size:.92rem;border:1px solid}
.msg.err{color:var(--err);border-color:var(--err)}.msg.ok{color:var(--ok);border-color:var(--ok)}
table{width:100%;border-collapse:collapse;font-size:.92rem}th,td{padding:8px 10px;border-bottom:1px solid var(--bd);text-align:left}
.scroll{overflow-x:auto}.top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
</style></head><body><div class="wrap">
<?php }
function pie(): void { echo '</div></body></html>'; }
