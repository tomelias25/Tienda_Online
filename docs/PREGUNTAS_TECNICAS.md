# Preguntas técnicas por rol

Preguntas que el docente puede hacer sobre lo que **cada integrante realizó**, con respuestas modelo basadas en este proyecto. Cada pregunta indica la materia a la que aplica: **[BD]** Bases de Datos, **[IS]** Ingeniería de Software.

## Preguntas comunes

**1. [IS] ¿Qué arquitectura usaron y por qué?**
Arquitectura en capas sobre páginas PHP: presentación (`login.php`, `registro.php`, `panel.php`), negocio (`autenticacion.php`, `validaciones.php`), acceso a datos (`config.php` con PDO) y base de datos MariaDB. No es MVC completo; se eligió algo simple para el alcance del semestre, separando la lógica de las páginas para poder probarla. Ver `docs/ARQUITECTURA.md`.

**2. [IS] ¿Qué diferencia hay entre una prueba unitaria y una de integración? ¿Cuáles hicieron?**
La unitaria prueba una función aislada, sin base de datos (grupo U: 18 casos). La de integración prueba el código junto con la base de datos real (grupos B, A y R: 31 casos). Total: 49 casos, todos aprobados. Ver `docs/PRUEBAS_UNITARIAS.md`.

**3. [IS] ¿Cómo se ejecutan las pruebas y por qué no tocan la base real?**
Con `php tests\run_tests.php`. El ejecutor crea `tienda_online_test` desde el `.sql` y la recarga antes de cada grupo, así que cada grupo parte de datos limpios y la base real no se modifica.

**4. [BD] ¿Qué problemas tenía el modelo original y cómo los resolvieron?**
Nueve de diez usuarios tenían la contraseña vacía; el correo no era único; no había stock, roles, estado ni total del pedido; el detalle no guardaba el precio; y las llaves foráneas admitían NULL. Se resolvió con hash bcrypt, `UNIQUE`, columnas nuevas, `CHECK` y `NOT NULL`.

**5. [IS] ¿Qué falta por implementar?**
Carrito y creación de pedidos con varios productos, e historial interactivo (el panel hoy lista los pedidos existentes). Están planificados en los sprints 2 y 3 del cronograma.

---

## Product Owner – Andrés Leonardo Ortega Díaz

**1. [IS] ¿Qué es el Product Backlog y cómo lo priorizaron?**
Es la lista ordenada de todo lo que necesita el producto. Se priorizó por riesgo y valor: primero seguridad y datos (login, registro, modelo), porque el diagnóstico mostró que el 90 % de las cuentas no tenía contraseña; después catálogo, carrito, pedidos e historial.

**2. [IS] Escriba una historia de usuario con sus criterios de aceptación.**
*Como cliente registrado, quiero iniciar sesión con mi correo y contraseña para ver mis pedidos.*
- Dado un correo y contraseña correctos, cuando envío el formulario, entonces entro al panel.
- Dada una contraseña incorrecta, entonces veo un mensaje genérico, sin saber si el correo existe.
- Dados 5 intentos fallidos seguidos, entonces la cuenta se bloquea 5 minutos.

**3. [IS] ¿Cuáles son los requisitos funcionales y no funcionales?**
Funcionales: RF01 registro, RF02 inicio de sesión, RF03 cierre de sesión, RF04 catálogo, RF05 pedidos según rol; pendientes: carrito y creación de pedidos.
No funcionales: seguridad (bcrypt, CSRF), integridad de datos, usabilidad (SUS ≥ 68), compatibilidad con XAMPP y mantenibilidad (pruebas automáticas).

**4. [IS] ¿Qué es la definición de terminado (DoD) y cuál usaron?**
Es el criterio común para considerar terminada una historia. La nuestra: funcionalidad probada, sin errores de integridad, con consultas preparadas y validación de entradas.

**5. [IS] ¿Cómo miden que el proyecto cumple el objetivo?**
Con indicadores: 100 % de cuentas con hash bcrypt, ≥ 90 % de casos de prueba aprobados, 0 vulnerabilidades críticas abiertas y SUS ≥ 68 con al menos 10 usuarios, 3 de ellos MiPymes de Cúcuta.

**6. [IS] ¿Qué es la escala SUS?**
*System Usability Scale*: cuestionario de 10 preguntas sobre usabilidad cuyo puntaje va de 0 a 100; 68 se toma como el promedio de referencia.

**7. [IS] ¿Cómo se relaciona el proyecto con los ODS?**
ODS 8 (meta 8.3, apoyo a las MiPymes), ODS 9 (meta 9.3, integración de pequeñas empresas a los mercados) y ODS 4 (meta 4.4, competencias técnicas para el empleo y el emprendimiento).

---

## Scrum Master – Maikoll Santiago Camacho Rodríguez

**1. [IS] ¿Cuáles son los eventos de Scrum y cómo los aplicaron?**
Planeación del sprint, reunión diaria breve, revisión del sprint y retrospectiva. Se trabajó con 3 sprints de una semana, cada uno con su planeación al inicio y su revisión y retrospectiva al final.

**2. [IS] ¿Qué es un impedimento? Dé ejemplos reales del proyecto.**
Algo que bloquea al equipo y que el Scrum Master debe remover. Ejemplos: al abrir el proyecto en otro computador apareció "Not Found" porque la carpeta no estaba en `htdocs` o su nombre no coincidía con la URL; y faltaba importar el `.sql` en el phpMyAdmin de ese equipo.

**3. [IS] ¿Para qué sirve la retrospectiva?**
Para identificar qué salió bien, qué mejorar y qué acuerdos tomar en el siguiente sprint. Ejemplo de acuerdo posible: separar la lógica de login y registro en funciones para poder probarla automáticamente.

**4. [IS] ¿Cómo usaron Git y GitHub?**
Rama `main` estable y ramas `feature/<tema>` por tarea, integradas con *pull request*; commits con prefijos (`feat:`, `fix:`, `docs:`, `test:`) y cada integrante con su propio usuario, para que `git log --author` muestre el aporte de cada uno.

**5. [IS] ¿Por qué Scrum y no cascada?**
Porque entrega incrementos funcionales cada semana, permite ajustar el rumbo con la retroalimentación y el semestre es corto. En cascada se vería el producto solo al final.

**6. [IS] ¿Cómo está organizada la documentación?**
`README.md` (instalación y estado), `docs/ARQUITECTURA.md`, `docs/PRUEBAS_UNITARIAS.md` y este documento de preguntas. El estado del proyecto se actualiza en el README al cierre de cada sprint.

**7. [IS] ¿Qué es la definición de roles en Scrum?**
Product Owner (qué se construye y en qué orden), Scrum Master (cómo trabaja el equipo, remueve impedimentos) y Desarrolladores (construyen el incremento).

---

## Desarrollador – Juan Sebastián Granados Contreras (base de datos y servidor)

**1. [BD] ¿Qué es la normalización y en qué forma normal está el modelo?**
Es organizar las tablas para reducir redundancia y anomalías. El modelo está en 3FN: valores atómicos (1FN), sin dependencias parciales (2FN) y sin dependencias transitivas (3FN). Por ejemplo, el nombre de la categoría vive en `categorias`, no repetido en cada producto.

**2. [BD] ¿Hay redundancias en el modelo? ¿Por qué?**
Sí, dos deliberadas: `precio_unitario` en `detalle_pedidos` (conserva el precio de la compra aunque el producto cambie) y `total` en `pedidos` (valor derivado para evitar recalcularlo). La consistencia del total se verifica con la prueba B11.

**3. [BD] ¿Qué es la integridad referencial?**
Garantía de que una llave foránea siempre apunta a una fila existente. Ejemplo: no se puede crear un pedido con `id_user` inexistente (prueba B05) ni borrar un producto ya vendido (prueba B12, comportamiento RESTRICT).

**4. [BD] ¿Por qué InnoDB?**
Porque soporta llaves foráneas y transacciones ACID; MyISAM no soporta llaves foráneas.

**5. [BD] ¿Diferencia entre PRIMARY KEY y UNIQUE? ¿Dónde las usaron?**
Ambas impiden duplicados, pero la PRIMARY KEY identifica la fila, es única por tabla y no admite NULL. Usamos PRIMARY KEY en `id_user`, `id_pedido`, etc., y UNIQUE en `usuarios.email` y `categorias.nombre_categoria`.

**6. [BD] ¿Qué son los CHECK y dónde los usaron?**
Restricciones que validan el valor de una columna. Usamos `stock >= 0`, `precio >= 0` y `cantidad > 0`; las pruebas B07 y B08 verifican que rechazan datos inválidos.

**7. [BD] Escriba una consulta de ventas por categoría.**
```sql
SELECT c.nombre_categoria AS categoria,
       SUM(d.cantidad * d.precio_unitario) AS ventas
FROM detalle_pedidos d
JOIN productos  p USING (id_producto)
JOIN categorias c USING (id_categoria)
GROUP BY c.id_categoria
ORDER BY ventas DESC;
```
Con los datos de ejemplo, la mayor es Muebles ($250.000), seguida de Tecnología ($178.000) y Ropa ($135.000).

**8. [BD] Escriba una consulta de pedidos y total por ciudad.**
```sql
SELECT u.ciudad, COUNT(*) AS pedidos, SUM(p.total) AS total
FROM pedidos p
JOIN usuarios u USING (id_user)
GROUP BY u.ciudad
ORDER BY total DESC;
```
Cúcuta lidera con 4 pedidos y $538.000.

**9. [BD] ¿Qué es una consulta preparada y por qué previene la inyección SQL?**
La sentencia se envía al servidor con marcadores (`?`) y los valores viajan aparte, de modo que el texto del usuario nunca se interpreta como SQL. En PDO usamos `prepare()` y `execute()`, con `ATTR_EMULATE_PREPARES = false`. La prueba A06 intenta `' OR '1'='1` y no autentica.

**10. [IS] ¿Por qué `intentar_login` recibe la conexión PDO como parámetro?**
Para poder probarla con una base de datos de prueba sin cambiar el código (inyección de dependencias manual). Si creara la conexión por dentro, las pruebas tocarían la base real.

**11. [BD] ¿Por qué la comparación del bloqueo de cuenta se hace en SQL y no en PHP?**
Porque la hora de PHP depende de `date.timezone` en `php.ini` y la de MariaDB de la zona del servidor; si difieren, el bloqueo puede no funcionar. Comparando con `NOW()` en SQL ambas horas salen de la misma fuente.

---

## Desarrollador – Juan Sebastián Pérez León (autenticación, interfaz y pruebas)

**1. [IS] ¿Por qué bcrypt y no MD5 o SHA-1?**
MD5 y SHA-1 son rápidos, y esa velocidad ayuda a quien prueba millones de combinaciones. bcrypt es lento a propósito, tiene sal incorporada (dos usuarios con la misma clave tienen hashes distintos) y su costo es ajustable. Se usa con `password_hash` y `password_verify`; por eso la columna es `VARCHAR(255)`.

**2. [IS] Explique el flujo de inicio de sesión paso a paso.**
Se muestra el formulario con un token CSRF; al enviarlo se valida el token; `intentar_login` valida el formato, busca al usuario con consulta preparada, revisa si está bloqueado, verifica el hash con `password_verify`, reinicia o suma intentos; si es correcto se regenera la sesión y se redirige al panel. Ver el diagrama de secuencia en `docs/ARQUITECTURA.md`.

**3. [IS] ¿Qué es CSRF y cómo lo previenen?**
Es un ataque en que otro sitio hace que el navegador del usuario envíe una petición a nuestra página con su sesión. Lo evitamos con un token aleatorio (`random_bytes(32)`) guardado en la sesión y enviado en un campo oculto, que se compara con `hash_equals`; además la cookie usa `SameSite=Lax`.

**4. [IS] ¿Para qué sirve `session_regenerate_id(true)`?**
Para cambiar el identificador de sesión al iniciar sesión y evitar la **fijación de sesión**, en la que un atacante impone un identificador conocido antes del login.

**5. [IS] ¿Por qué el mensaje de error es el mismo si el correo no existe?**
Para evitar la **enumeración de usuarios**: si el mensaje cambiara, un atacante podría descubrir qué correos están registrados. La prueba A04 lo verifica.

**6. [IS] ¿Cómo funciona el bloqueo por intentos?**
Cada fallo suma a `intentos_fallidos`; al quinto, se guarda `bloqueado_hasta = NOW() + 5 minutos` y el contador vuelve a 0. Mientras dure el bloqueo se rechaza incluso la contraseña correcta (prueba A10); al vencer, vuelve a permitir el ingreso (A11).

**7. [IS] ¿Qué es XSS y cómo lo previenen?**
Inyección de código JavaScript a través de datos que se muestran en la página. Todo dato impreso pasa por `e()` (`htmlspecialchars`), que convierte `<` y `>` en texto inofensivo. La prueba U14 lo verifica.

**8. [IS] ¿Cuántas pruebas hay y cómo comprobaron que sirven?**
49 casos, todos aprobados. Para comprobar que detectan errores se rompió el código a propósito (bloqueo un intento tarde, correo sin `UNIQUE`, contraseña sin cifrar) y las pruebas fallaron en cada caso. Ver la sección 6 de `docs/PRUEBAS_UNITARIAS.md`.

**9. [IS] ¿Cómo es la interfaz y por qué?**
Páginas con HTML y CSS propios, adaptables a pantallas pequeñas y con modo claro u oscuro según la preferencia del sistema (`prefers-color-scheme`). Los formularios tienen etiquetas (`label`) asociadas y los errores usan `role="alert"` para ser accesibles.

**10. [IS] ¿Qué pruebas faltan?**
Pruebas automáticas de la interfaz y de carga. Cuando se implemente el carrito, se agregan casos de descuento de stock, total calculado y rechazo por stock insuficiente.
