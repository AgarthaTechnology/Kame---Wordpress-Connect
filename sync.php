<?php
// No cerrar la etiqueta PHP al final del archivo

// ---------------------------
// Aumentar Límites de Recursos
// ---------------------------

ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en la salida
ini_set('log_errors', 1);     // Habilitar el registro de errores

// ---------------------------
// Definición de Rutas Dinámicas
// ---------------------------

// Obtener el directorio actual del script (raíz del plugin)
$plugin_dir = __DIR__;

// Definir la ruta al archivo de log relativo al plugin
$log_file = $plugin_dir . '/cron_sync_log.txt';
$log_handle = fopen($log_file, 'a');
if (!$log_handle) {
    error_log('No se pudo abrir el archivo de log: ' . $log_file);
    die('No se pudo abrir el archivo de log.');
}

// Función para registrar mensajes en el log
function log_message($message) {
    global $log_handle;
    fwrite($log_handle, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
}

// Iniciar registro
log_message("Iniciando el script de sincronización por cron.");

// Configurar el log de errores de PHP relativo al plugin
$error_log_file = $plugin_dir . '/error_log_sync_cron.php';
ini_set('error_log', $error_log_file);
log_message("Configuración de error_log completada.");

// ---------------------------
// Cargar WordPress
// ---------------------------

// Determinar la ruta base de WordPress
// Asumiendo que el plugin está en wp-content/plugins/WP-Kame-Connect-3.2.7/
$wp_load_file = dirname($plugin_dir, 3) . '/wp-load.php'; // Sube 3 niveles desde el plugin a la raíz de WordPress

// Verificar si wp-load.php existe
if (!file_exists($wp_load_file)) {
    log_message("ERROR: No se puede encontrar wp-load.php en la ruta especificada: $wp_load_file");
    fclose($log_handle);
    die('No se puede encontrar wp-load.php. Verifica la ruta base.');
}

// Cargar WordPress
log_message("Cargando WordPress desde: $wp_load_file");
require_once $wp_load_file;
log_message("WordPress cargado correctamente.");

// ---------------------------
// Verificar Archivo sync.php del Plugin
// ---------------------------

// Definir la ruta al archivo sync.php dentro del plugin
$plugin_sync_file = $plugin_dir . '/includes/api/sync.php';

// Verificar si sync.php existe
if (!file_exists($plugin_sync_file)) {
    log_message("ERROR: El archivo sync.php no existe en la ruta especificada: $plugin_sync_file");
    fclose($log_handle);
    exit(1);
}
log_message("Archivo sync.php encontrado: $plugin_sync_file");

// ---------------------------
// Ejecutar Sincronización
// ---------------------------

log_message("Iniciando sincronización...");
try {
    require_once $plugin_sync_file; // Incluir sync.php del plugin
    kame_erp_synchronize_inventory(); // Ejecutar la función de sincronización
    log_message("Sincronización completada exitosamente.");
} catch (Exception $e) {
    log_message("ERROR durante la sincronización: " . $e->getMessage());
}

// ---------------------------
// Finalizar Registro
// ---------------------------

log_message("Finalizando script de sincronización por cron.");
fclose($log_handle);
?>
