# SGPP-UNS: Sistema de Gestión de Proyectos y Productos Académicos
### Universidad Nacional del Santa (UNS) — Facultad de Ingeniería
**Escuela Profesional de Ingeniería de Sistemas e Informática**  
**Curso:** Sistemas de Información II  
**Docente:** Dr. Sixto Díaz Tello  
**Estudiante:** Erick Rodrigo Miranda Vega (Cód. 0202414028)  
**Semestre Académico:** 2026-II &bull; Nuevo Chimbote, Perú

---

## 1. Descripción del Proyecto y Objetivo
El **SGPP-UNS** es un prototipo web funcional desarrollado para la gestión integral de proyectos formativos, entregables y productos académicos desarrollados por estudiantes de la Universidad Nacional del Santa.

El sistema resuelve los problemas típicos en fechas de entrega de proyectos universitarios:
1. **Pérdida o desorden de documentos:** Almacenamiento físico estructurado en disco y metadatos en base de datos.
2. **Fallas durante revisiones docentes:** Uso de transacciones ACID (InnoDB) para garantizar que los registros confirmados nunca se pierdan.
3. **Vulnerabilidades de acceso a proyectos ajenos (Driver DR-01):** Validación estricta a nivel de la capa de aplicación en PHP impidiendo el acceso, modificación o descarga de archivos entre estudiantes.
4. **Monitoreo transversal:** Panel de supervisión en tiempo real de salud de la base de datos, métricas de almacenamiento y bitácora de auditoría.

---

## 2. Tecnologías Utilizadas
* **Lenguaje Backend:** PHP 8+ (Programación estructurada y modular con separación de responsabilidades).
* **Base de Datos:** MySQL / MariaDB (Motor InnoDB con soporte transaccional y claves foráneas).
* **Capa de Persistencia:** PHP Data Objects (PDO) con sentencias preparadas y transacciones seguras (`beginTransaction`, `commit`, `rollBack`).
* **Frontend:** HTML5, CSS3 nativo, JavaScript modular ligero.
* **Framework UI:** Bootstrap 5.3 + Bootstrap Icons (diseño responsivo para computadoras y tablets).
* **Identidad Institucional:** Paleta de colores oficial UNS:
  * **Color principal:** Burdeos / Rojo granate institucional (`#8B1527` / `#5C0B18`).
  * **Color secundario:** Dorado UNS (`#C59B27` / `#D4AF37`).
  * **Fondos:** Grises neutros claros (`#F4F6F9`) y blanco.
* **Almacenamiento:** Sistema de archivos del servidor web (`uploads/`), desacoplado de la base de datos.

---

## 3. Arquitectura del Sistema
El prototipo implementa la propuesta arquitectónica de la **Práctica #03**, dividida en 5 responsabilidades bien definidas:

```
                               ┌────────────────────────────────┐
                               │       Supervisión / Monitoreo   │
                               │   (Auditoría y Salud en vivo)  │
                               └───────────────┬────────────────┘
                                               │ Supervisa
                                               ▼
┌──────────────┐   Solicitudes    ┌─────────────────────────────┐
│  Usuarios    ├─────────────────►│        Interfaz Web         │
│(Estudiantes, │                  │ (Bootstrap 5, CSS UNS, JS)  │
│ Docentes,    │◄─────────────────┤                             │
│ Coord., Aut.)│   Respuestas     └──────────────┬──────────────┘
└──────────────┘                                 │ Solicitud HTTP / Form
                                                 ▼
                                  ┌─────────────────────────────┐
                                  │      Capa de Aplicación     │
                                  │   (PHP 8 + Control DR-01)   │
                                  └──────┬───────────────┬──────┘
                                         │               │
                     Consultar / Guardar │               │ Guardar / Descargar
                             Metadatos   ▼               ▼ Archivos Físicos
                                ┌─────────────────┐    ┌─────────────────┐
                                │  Base de Datos  │    │ Almacenamiento  │
                                │ MariaDB (InnoDB)│    │ Físico uploads/ │
                                └─────────────────┘    └─────────────────┘
```

### Estructura de Directorios
```
c:\xampp\htdocs\sgpp-uns\
├── config/
│   └── database.php           # Conexión Singleton PDO y verificación de salud (healthcheck)
├── models/
│   ├── Usuario.php            # Autenticación, password_verify() y perfiles
│   ├── Proyecto.php           # Lógica de proyectos y comprobación estricta DR-01
│   ├── Entregable.php         # Ciclo de vida y evaluación de entregables con transacciones
│   ├── Archivo.php            # Validación MIME, extensiones y guardado en uploads/
│   ├── Observacion.php        # Dictámenes y retroalimentación docente
│   └── Auditoria.php          # Bitácora de eventos y supervisión en tiempo real
├── views/
│   ├── auth/                  # Vistas de autenticación
│   ├── estudiante/            # Dashboard de estudiante, proyectos y entregables
│   ├── docente/               # Dashboard de docente asesor
│   ├── coordinador/           # Dashboard de coordinación y avance
│   ├── autoridad/             # Dashboard ejecutivo de decanatura
│   └── error/
│       └── 403.php            # Pantalla de Acceso No Autorizado por Driver DR-01
├── includes/
│   ├── session.php            # Control de sesión, CSRF, XSS helper y badges
│   ├── header.php             # Cabecera HTML y librerías
│   ├── sidebar.php            # Menú lateral dinámico según rol
│   ├── navbar.php             # Barra superior con usuario activo y semestre
│   ├── alerts.php             # Mensajes flash
│   └── footer.php             # Pie de página y cierre de etiquetas
├── assets/
│   ├── css/
│   │   └── uns-theme.css      # Hoja de estilos con identidad institucional UNS
│   ├── js/
│   │   └── main.js            # Interacciones, toggle responsive y demo autofill
│   └── img/
│       ├── logo_uns.svg       # Escudo emblemático oficial UNS vectorizado
│       └── login_bg.svg       # Fondo institucional para pantalla de login
├── uploads/                   # Almacenamiento físico de documentos fuera de MySQL
├── index.php                  # Enrutador principal según rol
├── login.php                  # Pantalla de acceso con selector rápido de usuarios demo
├── logout.php                 # Cierre de sesión seguro con auditoría
├── proyecto.php               # Detalle del proyecto y entregables (protegido con DR-01)
├── nuevo_proyecto.php         # Formulario de registro de proyecto
├── entregables.php            # Bandeja de entregables con filtros por estado
├── revisar.php                # Formulario docente de evaluación y dictamen
├── observaciones.php          # Bandeja de observaciones del estudiante
├── descargar.php              # Descarga segura de archivos con control DR-01
├── monitoreo.php              # Panel de monitoreo y bitácora de auditoría
├── reportes.php               # Reporte consolidado imprimible
├── database.sql               # Script SQL de creación e inserción de datos de prueba
├── tests_sistema.php          # Script de validación automatizada de drivers y funciones
└── README.md                  # Documentación técnica del proyecto
```

---

## 4. Requisitos y Puesta en Marcha en XAMPP

### Requisitos Previos
* XAMPP (versión con PHP 8.0 o superior y MariaDB/MySQL).
* Navegador web moderno (Chrome, Edge, Firefox).

### Pasos de Instalación
1. **Copiar el proyecto:**
   Asegúrese de que la carpeta esté ubicada exactamente en:
   ```
   C:\xampp\htdocs\sgpp-uns
   ```

2. **Iniciar Servicios en XAMPP:**
   Abra el **XAMPP Control Panel** e inicie los módulos:
   * **Apache** (debe quedar en verde).
   * **MySQL** (debe quedar en verde).

3. **Crear e Importar la Base de Datos:**
   * Opción A (Vía phpMyAdmin):
     1. Ingrese a `http://localhost/phpmyadmin/`.
     2. Haga clic en la pestaña **Importar**.
     3. Seleccione el archivo `database.sql` ubicado en `c:\xampp\htdocs\sgpp-uns\database.sql`.
     4. Presione **Continuar**.
   * Opción B (Línea de comandos rápida):
     ```powershell
     & "C:\xampp\mysql\bin\mysql.exe" -u root --default-character-set=utf8mb4 -e "source c:/xampp/htdocs/sgpp-uns/database.sql"
     ```

4. **Acceder a la Aplicación:**
   Abra su navegador web e ingrese a:
   ```
   http://localhost/sgpp-uns/
   ```

---

## 5. Cuentas de Acceso de Demostración
Todas las contraseñas están almacenadas mediante `password_hash()` con algoritmo BCRYPT y se verifican mediante `password_verify()`.

| Rol | Usuario | Contraseña | Nombre del Usuario / Cargo |
| :--- | :--- | :--- | :--- |
| **Estudiante 1** | `estudiante1` | `123456` | Erick Rodrigo Miranda Vega (Cód. 0202414028) |
| **Estudiante 2** | `estudiante2` | `123456` | Carlos Alberto Flores Valdivia (Cód. 0202414099) |
| **Docente** | `docente1` | `123456` | Dr. Sixto Díaz Tello (Docente Asesor / Revisor) |
| **Coordinador** | `coordinador1` | `123456` | Mag. Roberto Zavaleta Chávez (Coordinador EPISI) |
| **Autoridad** | `autoridad1` | `123456` | Dr. Jorge Domínguez Castañeda (Decanatura) |

> **Nota:** La pantalla de login incluye botones de selección rápida (*Accesos de Demostración*) para rellenar usuario y contraseña con un solo clic.

---

## 6. Demostración Paso a Paso del Driver Arquitectónico DR-01
El driver **DR-01** establece:
> *"Evitar que un estudiante pueda consultar o modificar proyectos que no le pertenecen en el 100 % de los intentos."*

### Procedimiento de Comprobación en Vivo:
1. Abra el navegador e ingrese a `http://localhost/sgpp-uns/login.php`.
2. Inicie sesión con el usuario `estudiante1` (contraseña: `123456`).
3. En el Dashboard del estudiante, observe sus proyectos asignados:
   * `PRY-2026-001` (ID = 1)
   * `PRY-2026-002` (ID = 2)
4. Haga clic en **"Ver Proyecto"** del proyecto ID 1. Verifique que la URL es:
   `http://localhost/sgpp-uns/proyecto.php?id=1`
   El estudiante puede visualizar perfectamente su título, entregables, observaciones y descargar sus archivos.
5. **Modifique manualmente la URL en el navegador:**
   Cambie `id=1` por `id=3` (el proyecto ID 3 pertenece al `estudiante2`):
   `http://localhost/sgpp-uns/proyecto.php?id=3`
6. **Compruebe el Comportamiento del Sistema:**
   * La capa de aplicación PHP intercepta la solicitud en el servidor.
   * El servidor devuelve el código de estado **HTTP 403 Forbidden**.
   * Se muestra la pantalla institucional de **"Acceso No Autorizado - Control de Seguridad DR-01"**.
   * **Ninguna información sensible del proyecto ID 3 es revelada ni enviada en el HTML.**
   * El estudiante tampoco puede modificar ni subir archivos a dicho proyecto.
7. **Verificación en el Módulo de Monitoreo:**
   * Haga clic en **"Monitoreo"** en el menú lateral o ingrese a `http://localhost/sgpp-uns/monitoreo.php`.
   * En la tabla de bitácora verá la fila registrada automáticamente:
     * **Usuario:** `estudiante1`
     * **Acción:** `Acceso no autorizado (DR-01)`
     * **Detalle:** `Intento denegado: El estudiante intentó acceder al proyecto ID #3 perteneciente a otro alumno.`
     * **Resultado:** `Rechazado` (en color rojo distintivo).

---

## 7. Decisiones de Arquitectura, Concurrencia y Confiabilidad

### A. Almacenamiento Desacoplado de Archivos (Decisión #4)
* Los documentos adjuntos a los entregables **no se almacenan como datos BLOB dentro de la base de datos**.
* Se guardan físicamente en el directorio `uploads/` con nombres únicos generados mediante hash y timestamp (`doc_{entregable_id}_{timestamp}_{uniqid}.ext`).
* En la tabla `archivos` únicamente se almacenan metadatos (`nombre_original`, `ruta`, `tipo_mime`, `tamano_bytes`, etc.).
* La descarga está controlada por `descargar.php`, que valida los permisos de acceso antes de transmitir los bytes al navegador.

### B. Confiabilidad y Recuperación ante Fallos (Decisión #3)
* Para garantizar que los registros confirmados no se pierdan, las operaciones críticas se ejecutan mediante **Transacciones ACID** de PDO en MySQL (InnoDB):
  * **Registro de nuevo proyecto:** Se realiza dentro de un bloque `beginTransaction() / commit()`.
  * **Carga de entregable y subida de archivos:** Se valida que si ocurre un error en la base de datos, el archivo físico temporal se limpie y la transacción haga `rollBack()`.
  * **Evaluación docente:** El cambio de estado del entregable, la inserción de la observación, el registro en el historial de estados y la bitácora de auditoría se confirman de forma atómica.
* *Nota Técnica:* En un entorno de producción a gran escala, la recuperación ante fallas requeriría además replicación de base de datos con réplicas de lectura, respaldos continuos automáticos (Point-In-Time Recovery) y sistemas de almacenamiento de objetos redundantes (Cloud Storage / S3).

### C. Rendimiento y Concurrencia (300 usuarios en fechas de entrega)
* Se establecieron índices relacionales explícitos en MySQL en las columnas de mayor consulta (`estudiante_id`, `estado`, `proyecto_id`, `entregable_id`, `usuario_id`, `fecha_hora`).
* Las consultas SQL utilizan cláusulas de selección puntuales y uniones `JOIN` indexadas, evitando consultas `N+1`.
* *Consideración de Escalabilidad:* Este prototipo académico representa una arquitectura inicial ejecutada en un servidor único XAMPP. La atención garantizada de 300 usuarios concurrentes reales en producción requeriría pruebas de carga con herramientas como Apache JMeter o k6, balanceo de carga entre múltiples instancias PHP-FPM y capas de caché de sesiones (Redis).

---

## 8. Verificación Automatizada del Sistema
El proyecto incluye un script de pruebas unitarias y de integración que puede ejecutarse directamente desde la terminal para comprobar que todas las funciones y reglas del caso universitario están operativas:

```powershell
php tests_sistema.php
```

Resultado obtenido:
```
====================================================================
   INICIANDO PRUEBAS DEL SISTEMA SGPP-UNS Y DRIVER ARQUITECTÓNICO DR-01
====================================================================

[OK] Conexión a Base de Datos MariaDB/MySQL mediante PDO activa (10.4.32-MariaDB)
[OK] Autenticación de 'estudiante1' exitosa con password_verify()
[OK] Autenticación de 'estudiante2' exitosa
[OK] Rechazo de credenciales inválidas para 'estudiante1'
[OK] Estudiante1 consulta exitosamente su propio proyecto ID #1
[OK] DR-01: estudiante1 NO puede consultar proyecto ID #3 ajeno. Acceso bloqueado en capa PHP.
[OK] DR-01: El intento de acceso no autorizado fue registrado automáticamente en historial_acciones con resultado 'Rechazado'
[OK] Registro transaccional de nuevo proyecto exitoso (PRY-2026-005)
[OK] Creación de entregable con transacción exitosa (ID #7)
[OK] Autenticación de 'docente1' (Dr. Sixto Díaz Tello)
[OK] Docente evaluó entregable como 'Observado' y registró dictamen transaccional
[OK] El estudiante visualiza la observación emitida por el docente en su bandeja
[OK] Módulo de monitoreo reporta correctamente los archivos almacenados físicamente (5 archivos, 1.17 MB)

====================================================================
   RESUMEN FINAL: Aciertos: 13 | Fallos: 0
====================================================================
¡TODAS LAS PRUEBAS FUNCIONALES Y DE ARQUITECTURA PASARON CON ÉXITO!
```

---

## 9. Conclusión del Caso Académico
El prototipo **SGPP-UNS** demuestra que una decisión arquitectónica no debe depender de trucos visuales en el navegador (como ocultar botones con CSS o JavaScript), sino que debe resolverse de manera determinista en la capa de aplicación y respaldarse en una persistencia transaccional y supervisión continua.
