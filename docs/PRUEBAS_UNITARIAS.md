# Pruebas unitarias y de integración

## 1. Estrategia

Las pruebas están en `tests/run_tests.php`. Es un ejecutor **sin dependencias** (no requiere Composer ni PHPUnit), de modo que corre con el PHP que trae XAMPP.

Se organizan en cuatro grupos:

| Grupo | Tipo | Casos | Qué verifica |
|---|---|---|---|
| **U** | Unitarias (sin base de datos) | 18 | Validaciones, escape de HTML, token CSRF |
| **B** | Integración con la base de datos | 14 | Restricciones del modelo: UNIQUE, FK, CHECK, NOT NULL, ENUM, integridad contable |
| **A** | Integración de autenticación | 12 | Login, mensajes genéricos, bloqueo por intentos, inyección SQL |
| **R** | Integración de registro | 5 | Cifrado de contraseña, rol por defecto, correo duplicado |
| | **Total** | **49** | |

Las pruebas **U** son unitarias en sentido estricto: ejecutan una función aislada, sin base de datos ni sesión real. Los grupos **B, A y R** son de **integración**, porque ejercitan el código junto con MariaDB real.

> Cumple el indicador del objetivo específico OE4 del PPA: mínimo 30 casos, con funcionales, de integridad y de seguridad.

## 2. Aislamiento

El ejecutor crea la base `tienda_online_test` a partir de `database/tienda_online.sql` y la recarga antes de cada grupo, de modo que cada grupo parte de datos limpios. **No toca la base `tienda_online` real.**

La conexión de prueba usa `sql_mode = STRICT_ALL_TABLES`, como un servidor de producción; sin ese modo MariaDB aceptaría silenciosamente valores inválidos en un `ENUM`.

## 3. Cómo ejecutarlas

1. Encienda **MySQL** en el panel de XAMPP.
2. En la terminal de VS Code (carpeta del proyecto):

```
C:\xampp\php\php.exe tests\run_tests.php
```

Código de salida: `0` si todo pasa, `1` si alguna prueba falla y `2` si no pudo conectarse a la base.

## 4. Casos de prueba

### Grupo U – Unitarias

| ID | Qué se prueba | Resultado esperado |
|---|---|---|
| U01 | `email_valido` con correo correcto | Acepta |
| U02 | `email_valido` sin `@` | Rechaza |
| U03 | `email_valido` con cadena vacía | Rechaza |
| U04 | `email_valido` con más de 80 caracteres | Rechaza |
| U05 | `contrasena_valida("Clave2026x")` | Acepta |
| U06 | Contraseña de menos de 8 caracteres | Rechaza |
| U07 | Contraseña sin números | Rechaza |
| U08 | Contraseña sin letras | Rechaza |
| U09 | `validar_registro` con datos correctos | 0 errores |
| U10 | Nombre vacío | 1 error |
| U11 | Nombre de 51 caracteres | 1 error |
| U12 | Contraseñas que no coinciden | 1 error |
| U13 | Ciudad de 51 caracteres | 1 error |
| U14 | `e()` con `<script>` | Devuelve el texto escapado (previene XSS) |
| U15 | `largo("Sofía")` | 5 (cuenta bien las tildes) |
| U16 | `csrf_ok` con el token correcto | Acepta |
| U17 | `csrf_ok` con otro token | Rechaza |
| U18 | `csrf_ok` con `null` | Rechaza |

### Grupo B – Base de datos

| ID | Qué se prueba | Resultado esperado |
|---|---|---|
| B01 | Existencia de las 5 tablas | 5 tablas |
| B02 | Usuarios con contraseña vacía | 0 |
| B03 | Los 10 hashes son bcrypt y verifican con la clave de demostración | 10 de 10 |
| B04 | Insertar un correo repetido | Error (UNIQUE) |
| B05 | Pedido con usuario inexistente | Error (FK) |
| B06 | Detalle con producto inexistente | Error (FK) |
| B07 | Stock negativo | Error (CHECK) |
| B08 | Cantidad igual a 0 | Error (CHECK) |
| B09 | Pedido con `id_user` NULL | Error (NOT NULL) |
| B10 | Rol `superadmin` | Error (ENUM) |
| B11 | Total del pedido = suma de cantidad × precio_unitario | 0 diferencias |
| B12 | Borrar un producto que ya se vendió | Error (RESTRICT) |
| B13 | Suma de ventas por categoría | $927.000 |
| B14 | Clientes en Cúcuta | 4 de 10 |

### Grupo A – Autenticación

| ID | Qué se prueba | Resultado esperado |
|---|---|---|
| A01 | Login del administrador | Ingresa con rol `administrador` |
| A02 | Login de un cliente | Ingresa con rol `cliente` |
| A03 | Contraseña incorrecta | Mensaje genérico |
| A04 | Correo inexistente | **El mismo** mensaje genérico |
| A05 | Correo vacío, con formato inválido o contraseña vacía | Rechaza |
| A06 | Inyección SQL (`' OR '1'='1`) | No autentica |
| A07 | Dos fallos seguidos | Contador en 2 |
| A08 | Login correcto tras fallos | Contador en 0 |
| A09 | Cinco fallos seguidos | Cuenta bloqueada |
| A10 | Cuenta bloqueada con clave correcta | Rechaza con mensaje de bloqueo |
| A11 | Bloqueo vencido | Vuelve a permitir el ingreso |
| A12 | Cuenta con contraseña vacía en la BD | No entra ni con contraseña vacía |

### Grupo R – Registro

| ID | Qué se prueba | Resultado esperado |
|---|---|---|
| R01 | Registro válido | Usuario creado con rol `cliente` |
| R02 | Contraseña guardada | Hash bcrypt, nunca texto plano |
| R03 | Login con la cuenta nueva | Ingresa |
| R04 | Correo duplicado | "Ese correo ya está registrado." |
| R05 | Datos inválidos | No inserta nada |

## 5. Resultado de la ejecución

Salida real, ejecutada en un entorno de desarrollo (PHP 8.3, MariaDB 10.11). El archivo completo está en [`RESULTADOS_PRUEBAS.txt`](RESULTADOS_PRUEBAS.txt):

```
Total: 49 | Aprobadas: 49 | Falladas: 0 | Aprobación: 100 %
```

> **Para la entrega:** ejecute las pruebas en su equipo con XAMPP y reemplace esta salida por la suya, con la fecha.

## 6. Comprobación de que las pruebas detectan errores

Para verificar que las pruebas no pasan "por pasar", se introdujeron fallos a propósito y se confirmó que fallan:

| Fallo introducido | Pruebas que lo detectaron |
|---|---|
| El bloqueo se activa un intento tarde (`>` en vez de `>=`) | A09, A10 |
| Se quita `UNIQUE` al correo en el script SQL | B04, R04 |
| El registro guarda la contraseña sin cifrar | R02, R03 |

## 7. Limitaciones

- No hay pruebas automáticas de la interfaz (HTML); esa parte se valida manualmente y con la encuesta de usabilidad SUS.
- No hay pruebas de carga ni de concurrencia.
- Cuando se implementen carrito y pedidos con varios productos, se deben agregar casos (por ejemplo: descuento de stock, total calculado, rechazo por stock insuficiente).
