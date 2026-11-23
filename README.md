# Tienda Online – PPA (Proyecto Pedagógico de Aula)

Plataforma web de comercio electrónico para **MiPymes de Cúcuta**, con base de datos relacional íntegra y autenticación segura.

- **Institución / semillero:** FESC – IDSE (Innovación en Desarrollo de Software Empresarial)
- **Programa:** Ingeniería de Software · Tercer semestre
- **Materias que integra:** Bases de Datos e Ingeniería de Software
- **Empresa (formato PPA):** XXXXX *(se cambia en `config.php`, constante `NOMBRE_EMPRESA`)*
- **ODS:** 4 (Educación de calidad), 8 (Trabajo decente y crecimiento económico), 9 (Industria, innovación e infraestructura)

## Equipo y roles Scrum

| Integrante | Rol Scrum | Responsabilidad técnica |
|---|---|---|
| Andrés Leonardo Ortega Díaz | Product Owner | Backlog, historias de usuario, criterios de aceptación, validación con usuarios |
| Maikoll Santiago Camacho Rodríguez | Scrum Master | Eventos Scrum, impedimentos, repositorio y documentación |
| Juan Sebastián Granados Contreras | Desarrollador | Base de datos (modelo relacional, script SQL) y lógica del servidor |
| Juan Sebastián Pérez León | Desarrollador | Autenticación, interfaz y pruebas |

Docente: Ruth Estrella Cacua Hernández.

## Estado del proyecto

| Funcionalidad | Estado |
|---|---|
| Modelo relacional rediseñado (stock, estado/total de pedido, precio histórico, roles, restricciones) | Implementado |
| Registro, inicio y cierre de sesión (bcrypt, CSRF, bloqueo por intentos) | Implementado |
| Panel con catálogo y pedidos según rol (cliente / administrador) | Implementado |
| Pruebas unitarias y de integración (49 casos) | Implementado |
| Carrito y creación de pedidos con varios productos | Pendiente (sprints 2 y 3 del PPA) |
| Historial de pedidos interactivo | Pendiente (el panel ya lista los pedidos existentes) |

> Actualice esta tabla a medida que avancen los sprints.

## Estructura del repositorio

```
.
├── config.php            # Configuración, conexión PDO, sesión y funciones comunes (e, CSRF)
├── validaciones.php      # Reglas de validación puras (probadas con pruebas unitarias)
├── autenticacion.php     # Lógica de login y registro (recibe PDO por parámetro)
├── login.php             # Página de inicio de sesión
├── registro.php          # Página de registro
├── panel.php             # Panel: catálogo y pedidos
├── logout.php            # Cierre de sesión
├── database/
│   └── tienda_online.sql # Esquema + datos de ejemplo
├── tests/
│   └── run_tests.php     # Pruebas unitarias y de integración (sin dependencias)
└── docs/
    ├── ARQUITECTURA.md
    ├── PRUEBAS_UNITARIAS.md
    ├── PREGUNTAS_TECNICAS.md
    └── RESULTADOS_PRUEBAS.txt
```

## Requisitos

- XAMPP con Apache, PHP 8.2 o superior y MariaDB/MySQL (extensión `pdo_mysql` activa)
- Visual Studio Code (opcional, recomendado con la extensión *PHP Intelephense*)

## Instalación (XAMPP)

1. Clone o descargue el repositorio dentro de `C:\xampp\htdocs\tienda_online_php`.
2. Inicie **Apache** y **MySQL** desde el panel de XAMPP.
3. En `http://localhost/phpmyadmin` → **Importar** → seleccione `database/tienda_online.sql`.
4. Abra `http://localhost/tienda_online_php/login.php`.

Si su MySQL tiene contraseña para `root`, cámbiela en `config.php` (`DB_PASS`).

### Usuarios de prueba (contraseña de todos: `Tienda2026*`)

| Rol | Correo |
|---|---|
| Administrador | `laura.gomez@mail.com` |
| Cliente | `ana.rodriguez@mail.com` |

> Contraseña solo para demostración. Cámbiela en cualquier uso real.

## Ejecutar las pruebas

Con MySQL encendido:

```
C:\xampp\php\php.exe tests\run_tests.php
```

Las pruebas crean una base aparte (`tienda_online_test`) y **no tocan** la base real. Detalle en [`docs/PRUEBAS_UNITARIAS.md`](docs/PRUEBAS_UNITARIAS.md).

## Documentación

- [Arquitectura](docs/ARQUITECTURA.md)
- [Pruebas unitarias](docs/PRUEBAS_UNITARIAS.md)
- [Preguntas técnicas por rol](docs/PREGUNTAS_TECNICAS.md)

## Flujo de trabajo con Git

- Rama `main`: versión estable que se entrega.
- Ramas `feature/<tema>` por tarea (por ejemplo `feature/login`, `feature/modelo-datos`), integradas con *pull request*.
- Cada integrante hace sus *commits* con su propio usuario de Git; así `git log --author="nombre"` evidencia el aporte de cada rol.
- Mensajes de commit en imperativo y con prefijo: `feat:`, `fix:`, `docs:`, `test:`.
