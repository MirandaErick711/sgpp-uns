<?php
/**
 * Script de Verificación de Integridad y Validación del Driver DR-01
 * SGPP-UNS - Universidad Nacional del Santa
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Entregable.php';
require_once __DIR__ . '/models/Archivo.php';
require_once __DIR__ . '/models/Observacion.php';
require_once __DIR__ . '/models/Auditoria.php';

echo "====================================================================\n";
echo "   INICIANDO PRUEBAS DEL SISTEMA SGPP-UNS Y DRIVER ARQUITECTÓNICO DR-01\n";
echo "====================================================================\n\n";

$errores = 0;
$aciertos = 0;

function assertTest(bool $condicion, string $descripcion) {
    global $errores, $aciertos;
    if ($condicion) {
        echo "[OK] " . $descripcion . "\n";
        $aciertos++;
    } else {
        echo "[FALLO] " . $descripcion . "\n";
        $errores++;
    }
}

// 1. Prueba de Salud de Base de Datos
$health = Database::checkHealth();
assertTest($health['ok'] === true, "Conexión a Base de Datos MariaDB/MySQL mediante PDO activa ({$health['version']})");

// 2. Autenticación de Estudiante 1
$est1 = Usuario::autenticar('estudiante1', '123456');
assertTest($est1 !== null && $est1['usuario'] === 'estudiante1', "Autenticación de 'estudiante1' exitosa con password_verify()");

// 3. Autenticación de Estudiante 2
$est2 = Usuario::autenticar('estudiante2', '123456');
assertTest($est2 !== null && $est2['usuario'] === 'estudiante2', "Autenticación de 'estudiante2' exitosa");

// 4. Autenticación con contraseña errónea (debe fallar)
$loginInvalido = Usuario::autenticar('estudiante1', 'clave_erronea');
assertTest($loginInvalido === null, "Rechazo de credenciales inválidas para 'estudiante1'");

// 5. Consulta autorizada: estudiante1 consulta su proyecto #1 (Biblioteca)
$_SESSION['usuario_id'] = $est1['id'];
$_SESSION['usuario_login'] = $est1['usuario'];
$_SESSION['usuario_rol'] = 'estudiante';
$_SESSION['usuario_nombre_completo'] = $est1['nombres'] . ' ' . $est1['apellidos'];

$errorDr01 = null;
$pry1 = Proyecto::obtenerPorIdConSeguridad(1, $est1['id'], 'estudiante', $errorDr01);
assertTest($pry1 !== null && (int)$pry1['id'] === 1, "Estudiante1 consulta exitosamente su propio proyecto ID #1");

// ====================================================================
// 6. PRUEBA CRÍTICA DEL DRIVER ARQUITECTÓNICO DR-01:
// estudiante1 intenta acceder al proyecto ID #3 (pertenece a estudiante2)
// ====================================================================
$errorDr01 = null;
$pryAjeno = Proyecto::obtenerPorIdConSeguridad(3, $est1['id'], 'estudiante', $errorDr01);
assertTest(
    $pryAjeno === null && $errorDr01 === 'ACCESO_NO_AUTORIZADO_DR01',
    "DR-01: estudiante1 NO puede consultar proyecto ID #3 ajeno. Acceso bloqueado en capa PHP."
);

// 7. Verificar que el intento no autorizado fue auditado
$ultimosLogs = Auditoria::obtenerUltimos(10, 'Rechazado');
$dr01Auditado = false;
foreach ($ultimosLogs as $log) {
    if (strpos($log['accion'], 'DR-01') !== false && $log['nombre_usuario'] === 'estudiante1') {
        $dr01Auditado = true;
        break;
    }
}
assertTest($dr01Auditado, "DR-01: El intento de acceso no autorizado fue registrado automáticamente en historial_acciones con resultado 'Rechazado'");

// 8. Crear un nuevo proyecto con transacción
$resNuevoPry = Proyecto::crear([
    'titulo'              => 'Sistema de Predicción de Rendimiento con IA en la UNS',
    'descripcion'         => 'Investigación formativa para el curso de Sistemas de Información II utilizando árboles de decisión.',
    'linea_investigacion' => 'Ciencia de Datos e Inteligencia Artificial',
    'fecha_inicio'        => '2026-09-29',
    'fecha_fin_prevista'  => '2026-12-30'
], $est1['id'], $est1['usuario']);

assertTest($resNuevoPry['ok'] === true && !empty($resNuevoPry['codigo']), "Registro transaccional de nuevo proyecto exitoso ({$resNuevoPry['codigo']})");
$nuevoPryId = $resNuevoPry['id'];

// 9. Crear entregable para el nuevo proyecto
$resNuevoEntregable = Entregable::crear($nuevoPryId, 'Entregable 1: Definición de Variables y Preprocesamiento', 'Dataset institucional anonimizado.', $est1['id'], $est1['usuario']);
assertTest($resNuevoEntregable['ok'] === true, "Creación de entregable con transacción exitosa (ID #{$resNuevoEntregable['id']})");
$nuevoEntregableId = $resNuevoEntregable['id'];

// 10. Docente evalúa el entregable
$doc1 = Usuario::autenticar('docente1', '123456');
assertTest($doc1 !== null && $doc1['rol_nombre'] === 'docente', "Autenticación de 'docente1' (Dr. Sixto Díaz Tello)");

$resEval = Entregable::evaluarPorDocente(
    $nuevoEntregableId,
    $doc1['id'],
    'Observado',
    'Se requiere detallar los hiperparámetros del modelo predictivo y las métricas F1-Score.',
    $doc1['usuario']
);
assertTest($resEval['ok'] === true, "Docente evaluó entregable como 'Observado' y registró dictamen transaccional");

// 11. Verificar que el estudiante ahora visualiza la observación
$obsEstudiante = Observacion::obtenerPorEstudiante($est1['id']);
$obsEncontrada = false;
foreach ($obsEstudiante as $o) {
    if ((int)$o['entregable_id'] === $nuevoEntregableId && $o['tipo_decision'] === 'Observado') {
        $obsEncontrada = true;
        break;
    }
}
assertTest($obsEncontrada, "El estudiante visualiza la observación emitida por el docente en su bandeja");

// 12. Métricas y Monitoreo
$almacenamiento = Archivo::estadisticasAlmacenamiento();
assertTest($almacenamiento['total_archivos'] > 0, "Módulo de monitoreo reporta correctamente los archivos almacenados físicamente ({$almacenamiento['total_archivos']} archivos, {$almacenamiento['espacio_legible']})");

echo "\n====================================================================\n";
echo "   RESUMEN FINAL: Aciertos: {$aciertos} | Fallos: {$errores}\n";
echo "====================================================================\n";

if ($errores === 0) {
    echo "¡TODAS LAS PRUEBAS FUNCIONALES Y DE ARQUITECTURA PASARON CON ÉXITO!\n";
    exit(0);
} else {
    echo "Hubo errores en la ejecución de las pruebas.\n";
    exit(1);
}
