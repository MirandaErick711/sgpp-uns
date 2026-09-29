-- ====================================================================
-- SISTEMA DE GESTIÓN DE PROYECTOS Y PRODUCTOS ACADÉMICOS (SGPP-UNS)
-- UNIVERSIDAD NACIONAL DEL SANTA - FACULTAD DE INGENIERÍA
-- ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMAS E INFORMÁTICA
-- ====================================================================
-- Base de datos: sgpp_uns
-- Motor: MariaDB / MySQL (InnoDB con soporte transaccional y claves foráneas)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `sgpp_uns` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sgpp_uns`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `historial_acciones`;
DROP TABLE IF EXISTS `historial_estados`;
DROP TABLE IF EXISTS `observaciones`;
DROP TABLE IF EXISTS `archivos`;
DROP TABLE IF EXISTS `entregables`;
DROP TABLE IF EXISTS `proyectos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. TABLA: roles
-- --------------------------------------------------------------------
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. TABLA: usuarios
-- --------------------------------------------------------------------
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rol_id` INT NOT NULL,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `codigo_universitario` VARCHAR(20) NULL,
    `escuela` VARCHAR(150) DEFAULT 'Ingeniería de Sistemas e Informática',
    `facultad` VARCHAR(150) DEFAULT 'Facultad de Ingeniería',
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_usuarios_usuario` ON `usuarios` (`usuario`);
CREATE INDEX `idx_usuarios_rol` ON `usuarios` (`rol_id`);

-- --------------------------------------------------------------------
-- 3. TABLA: proyectos
-- --------------------------------------------------------------------
CREATE TABLE `proyectos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_proyecto` VARCHAR(30) NOT NULL UNIQUE,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `estudiante_id` INT NOT NULL,
    `linea_investigacion` VARCHAR(150) DEFAULT 'Sistemas de Información y Gestión del Conocimiento',
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin_prevista` DATE NOT NULL,
    `estado` ENUM('Pendiente', 'En revisión', 'Observado', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_proyectos_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_proyectos_estudiante` ON `proyectos` (`estudiante_id`);
CREATE INDEX `idx_proyectos_estado` ON `proyectos` (`estado`);
CREATE INDEX `idx_proyectos_fecha_inicio` ON `proyectos` (`fecha_inicio`);

-- --------------------------------------------------------------------
-- 4. TABLA: entregables
-- --------------------------------------------------------------------
CREATE TABLE `entregables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `proyecto_id` INT NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `descripcion` TEXT NULL,
    `numero_entregable` INT NOT NULL DEFAULT 1,
    `fecha_entrega` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `estado` ENUM('Pendiente', 'En revisión', 'Observado', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    `docente_revisor_id` INT NULL,
    `fecha_revision` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_entregables_proyecto` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_entregables_docente` FOREIGN KEY (`docente_revisor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_entregables_proyecto` ON `entregables` (`proyecto_id`);
CREATE INDEX `idx_entregables_estado` ON `entregables` (`estado`);

-- --------------------------------------------------------------------
-- 5. TABLA: archivos (Almacenamiento de metadatos de archivos físicos)
-- --------------------------------------------------------------------
CREATE TABLE `archivos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `entregable_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `nombre_original` VARCHAR(255) NOT NULL,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `ruta` VARCHAR(255) NOT NULL,
    `tipo_mime` VARCHAR(100) NOT NULL,
    `tamano_bytes` BIGINT NOT NULL,
    `fecha_subida` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_archivos_entregable` FOREIGN KEY (`entregable_id`) REFERENCES `entregables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_archivos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_archivos_entregable` ON `archivos` (`entregable_id`);
CREATE INDEX `idx_archivos_usuario` ON `archivos` (`usuario_id`);

-- --------------------------------------------------------------------
-- 6. TABLA: observaciones
-- --------------------------------------------------------------------
CREATE TABLE `observaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `entregable_id` INT NOT NULL,
    `docente_id` INT NOT NULL,
    `comentario` TEXT NOT NULL,
    `tipo_decision` ENUM('Aprobado', 'Observado', 'Rechazado') NOT NULL,
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_observaciones_entregable` FOREIGN KEY (`entregable_id`) REFERENCES `entregables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_observaciones_docente` FOREIGN KEY (`docente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_observaciones_entregable` ON `observaciones` (`entregable_id`);

-- --------------------------------------------------------------------
-- 7. TABLA: historial_estados (Trazabilidad del ciclo de vida)
-- --------------------------------------------------------------------
CREATE TABLE `historial_estados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `entregable_id` INT NOT NULL,
    `estado_anterior` VARCHAR(50) NULL,
    `estado_nuevo` VARCHAR(50) NOT NULL,
    `usuario_id` INT NOT NULL,
    `motivo` VARCHAR(255) NULL,
    `fecha_cambio` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_historial_estados_entregable` FOREIGN KEY (`entregable_id`) REFERENCES `entregables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_historial_estados_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_historial_estados_entregable` ON `historial_estados` (`entregable_id`);

-- --------------------------------------------------------------------
-- 8. TABLA: historial_acciones (Auditoría y Monitoreo del Sistema)
--    Permite demostrar la supervisión y el cumplimiento de DR-01
-- --------------------------------------------------------------------
CREATE TABLE `historial_acciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `nombre_usuario` VARCHAR(50) NOT NULL,
    `rol` VARCHAR(50) NOT NULL,
    `accion` VARCHAR(100) NOT NULL,
    `detalle` TEXT NULL,
    `resultado` ENUM('Correcto', 'Rechazado', 'Advertencia') NOT NULL DEFAULT 'Correcto',
    `ip_origen` VARCHAR(45) DEFAULT '127.0.0.1',
    `fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_historial_acciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_historial_usuario` ON `historial_acciones` (`usuario_id`);
CREATE INDEX `idx_historial_accion` ON `historial_acciones` (`accion`);
CREATE INDEX `idx_historial_resultado` ON `historial_acciones` (`resultado`);
CREATE INDEX `idx_historial_fecha` ON `historial_acciones` (`fecha_hora`);

-- ====================================================================
-- INSERCIÓN DE DATOS DE DEMOSTRACIÓN (SEEDERS)
-- Contraseñas encriptadas con password_hash('123456', PASSWORD_BCRYPT)
-- Hash: $2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW
-- ====================================================================

-- 1. Roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'estudiante', 'Estudiante de la Escuela Profesional de Ingeniería de Sistemas e Informática'),
(2, 'docente', 'Docente asesor / evaluador de proyectos y entregables académicos'),
(3, 'coordinador', 'Coordinador académico de investigación y proyectos de la escuela'),
(4, 'autoridad', 'Decanatura de Ingeniería y Dirección de Escuela UNS');

-- 2. Usuarios de Prueba
INSERT INTO `usuarios` (`id`, `rol_id`, `usuario`, `password`, `nombres`, `apellidos`, `email`, `codigo_universitario`, `escuela`, `facultad`, `activo`) VALUES
(1, 1, 'estudiante1', '$2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW', 'Erick Rodrigo', 'Miranda Vega', 'emiranda@uns.edu.pe', '0202414028', 'Ingeniería de Sistemas e Informática', 'Facultad de Ingeniería', 1),
(2, 1, 'estudiante2', '$2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW', 'Carlos Alberto', 'Flores Valdivia', 'cflores@uns.edu.pe', '0202414099', 'Ingeniería de Sistemas e Informática', 'Facultad de Ingeniería', 1),
(3, 2, 'docente1', '$2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW', 'Sixto', 'Díaz Tello', 'sdiaz@uns.edu.pe', 'DOC-0042', 'Ingeniería de Sistemas e Informática', 'Facultad de Ingeniería', 1),
(4, 3, 'coordinador1', '$2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW', 'Roberto', 'Zavaleta Chávez', 'rzavaleta@uns.edu.pe', 'COORD-0015', 'Ingeniería de Sistemas e Informática', 'Facultad de Ingeniería', 1),
(5, 4, 'autoridad1', '$2y$12$xJqiBVpC7UIDAy06CwgWEepuZ5nyrWICEAgbPL9agncJbc.r62poW', 'Jorge', 'Domínguez Castañeda', 'jdominguez@uns.edu.pe', 'AUT-0003', 'Ingeniería de Sistemas e Informática', 'Facultad de Ingeniería', 1);

-- 3. Proyectos
INSERT INTO `proyectos` (`id`, `codigo_proyecto`, `titulo`, `descripcion`, `estudiante_id`, `linea_investigacion`, `fecha_inicio`, `fecha_fin_prevista`, `estado`) VALUES
(1, 'PRY-2026-001', 'Sistema de Gestión de Biblioteca Universitaria y Préstamo Automatizado', 'Desarrollo de un sistema web integral para la digitalización de fichas bibliográficas, reserva en línea de ejemplares y trazabilidad de préstamos en la Biblioteca Central UNS.', 1, 'Sistemas de Información y Gestión del Conocimiento', '2026-08-15', '2026-12-20', 'En revisión'),
(2, 'PRY-2026-002', 'Plataforma de Reserva de Laboratorios de Cómputo para la EPISI', 'Implementación de un sistema automatizado de control de horarios, estaciones de trabajo y asignación de recursos en los laboratorios de cómputo de la Escuela de Sistemas.', 1, 'Ingeniería de Software y Tecnologías Emergentes', '2026-09-01', '2027-01-30', 'Pendiente'),
(3, 'PRY-2026-003', 'Sistema de Monitoreo de Calidad de Agua en los Valles del Santa', 'Prototipo IoT y aplicación web para la recolección telemétrica de parámetros físico-químicos del agua para riego en la provincia del Santa.', 2, 'Automatización, Redes y Telemática', '2026-07-10', '2026-11-28', 'Aprobado'),
(4, 'PRY-2026-004', 'Portal de Bolsa de Trabajo y Seguimiento del Egresado Santeño', 'Módulo de vinculación profesional, seguimiento de graduados y análisis de empleabilidad para egresados de la Universidad Nacional del Santa.', 2, 'Sistemas de Información y Gestión del Conocimiento', '2026-08-01', '2026-12-15', 'Observado');

-- 4. Entregables
INSERT INTO `entregables` (`id`, `proyecto_id`, `titulo`, `descripcion`, `numero_entregable`, `fecha_entrega`, `estado`, `docente_revisor_id`, `fecha_revision`) VALUES
-- Entregables Proyecto 1 (estudiante1)
(1, 1, 'Entregable 1: Documento SRS y Drivers Arquitectónicos', 'Especificación de requisitos de software (SRS) según IEEE 830, identificación de drivers arquitectónicos y atributos de calidad.', 1, '2026-08-25 10:30:00', 'Aprobado', 3, '2026-08-28 16:45:00'),
(2, 1, 'Entregable 2: Prototipo Funcional de la Capa de Seguridad y DR-01', 'Implementación del control de acceso en la capa de aplicación PHP y verificación de aislamiento de proyectos entre estudiantes.', 2, '2026-09-20 14:15:00', 'En revisión', 3, NULL),
(3, 1, 'Entregable 3: Modelo Relacional y Manual de Despliegue', 'Diagramas relacionales normalizados, diccionario de datos y manual de instalación en servidor Apache local.', 3, '2026-09-28 09:00:00', 'Pendiente', NULL, NULL),
-- Entregables Proyecto 2 (estudiante1)
(4, 2, 'Entregable 1: Plan de Trabajo y Cronograma de Desarrollo', 'Definición de objetivos, metodología ágil, matriz de roles y cronograma general de actividades de investigación.', 1, '2026-09-10 11:20:00', 'Pendiente', NULL, NULL),
-- Entregables Proyecto 3 (estudiante2)
(5, 3, 'Entregable 1: Informe de Arquitectura Telemétrica y Sensores', 'Diseño de la red de sensores y diagrama de bloques de comunicación telemétrica con base de datos central.', 1, '2026-08-10 09:00:00', 'Aprobado', 3, '2026-08-15 11:30:00'),
-- Entregables Proyecto 4 (estudiante2)
(6, 4, 'Entregable 1: Diagramas de Casos de Uso y Wireframes', 'Modelo funcional con especificación de casos de uso y maquetas preliminares de interfaz de usuario.', 1, '2026-08-20 15:40:00', 'Observado', 3, '2026-08-23 18:10:00');

-- 5. Archivos físicos registrados
INSERT INTO `archivos` (`id`, `entregable_id`, `usuario_id`, `nombre_original`, `nombre_archivo`, `ruta`, `tipo_mime`, `tamano_bytes`, `fecha_subida`) VALUES
(1, 1, 1, 'SRS_Biblioteca_SGPP_UNS_v1.pdf', 'arch_demo_1_srs_biblioteca.pdf', 'uploads/arch_demo_1_srs_biblioteca.pdf', 'application/pdf', 154820, '2026-08-25 10:31:00'),
(2, 2, 1, 'DR01_Seguridad_Aplicacion_PHP.pdf', 'arch_demo_2_dr01_seguridad.pdf', 'uploads/arch_demo_2_dr01_seguridad.pdf', 'application/pdf', 241500, '2026-09-20 14:16:00'),
(3, 4, 1, 'Plan_Trabajo_Laboratorios_EPISI.docx', 'arch_demo_4_plan_trabajo.docx', 'uploads/arch_demo_4_plan_trabajo.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 98500, '2026-09-10 11:21:00'),
(4, 5, 2, 'Diseno_Telemetrico_Agua_Santa.pdf', 'arch_demo_5_telemetria.pdf', 'uploads/arch_demo_5_telemetria.pdf', 'application/pdf', 312000, '2026-08-10 09:02:00'),
(5, 6, 2, 'Wireframes_Portal_Egresados.pdf', 'arch_demo_6_wireframes.pdf', 'uploads/arch_demo_6_wireframes.pdf', 'application/pdf', 420100, '2026-08-20 15:42:00');

-- 6. Observaciones docentes
INSERT INTO `observaciones` (`id`, `entregable_id`, `docente_id`, `comentario`, `tipo_decision`, `fecha_registro`) VALUES
(1, 1, 3, 'El SRS cumple con los estándares exigidos. Los drivers arquitectónicos reflejan adecuadamente los requisitos de disponibilidad y seguridad DR-01. Trabajo aprobado.', 'Aprobado', '2026-08-28 16:45:00'),
(2, 6, 3, 'Se requiere corregir la navegación en los wireframes del perfil de egresado y detallar las validaciones de seguridad para contraseñas.', 'Observado', '2026-08-23 18:10:00'),
(3, 5, 3, 'La arquitectura telemétrica es sólida y viable para la zona del Santa. Aprobado.', 'Aprobado', '2026-08-15 11:30:00');

-- 7. Historial de estados
INSERT INTO `historial_estados` (`id`, `entregable_id`, `estado_anterior`, `estado_nuevo`, `usuario_id`, `motivo`, `fecha_cambio`) VALUES
(1, 1, 'Pendiente', 'En revisión', 1, 'Envío de entregable inicial por el estudiante', '2026-08-25 10:30:00'),
(2, 1, 'En revisión', 'Aprobado', 3, 'Revisión y validación por docente Sixto Díaz Tello', '2026-08-28 16:45:00'),
(3, 2, 'Pendiente', 'En revisión', 1, 'Carga de prototipo de seguridad para evaluación', '2026-09-20 14:15:00'),
(4, 6, 'Pendiente', 'En revisión', 2, 'Envío de documento de diseño por estudiante', '2026-08-20 15:40:00'),
(5, 6, 'En revisión', 'Observado', 3, 'Observaciones formuladas por docente evaluador', '2026-08-23 18:10:00');

-- 8. Historial de acciones (Auditoría / Monitoreo)
INSERT INTO `historial_acciones` (`id`, `usuario_id`, `nombre_usuario`, `rol`, `accion`, `detalle`, `resultado`, `ip_origen`, `fecha_hora`) VALUES
(1, 1, 'estudiante1', 'estudiante', 'Inicio de sesión', 'Autenticación exitosa en la plataforma SGPP-UNS', 'Correcto', '127.0.0.1', '2026-09-28 08:30:12'),
(2, 1, 'estudiante1', 'estudiante', 'Registro de proyecto', 'Creación del proyecto PRY-2026-001 (Biblioteca Universitaria)', 'Correcto', '127.0.0.1', '2026-09-28 08:35:40'),
(3, 1, 'estudiante1', 'estudiante', 'Subida de archivo', 'Carga de documento SRS_Biblioteca_SGPP_UNS_v1.pdf al Entregable 1', 'Correcto', '127.0.0.1', '2026-09-28 08:42:19'),
(4, 3, 'docente1', 'docente', 'Inicio de sesión', 'Autenticación exitosa del docente Sixto Díaz Tello', 'Correcto', '127.0.0.1', '2026-09-28 09:15:04'),
(5, 3, 'docente1', 'docente', 'Aprobación de entregable', 'Aprobación del Entregable 1 de PRY-2026-001 y registro de observación', 'Correcto', '127.0.0.1', '2026-09-28 09:30:22'),
(6, 1, 'estudiante1', 'estudiante', 'Acceso no autorizado (DR-01)', 'Intento de consulta directa mediante URL a proyecto ajeno ID #3 (pertenece a estudiante2). Bloqueado por capa de aplicación.', 'Rechazado', '127.0.0.1', '2026-09-28 10:14:55'),
(7, 4, 'coordinador1', 'coordinador', 'Consulta de reportes', 'Generación de reporte general de avance de proyectos', 'Correcto', '127.0.0.1', '2026-09-28 11:05:30');
