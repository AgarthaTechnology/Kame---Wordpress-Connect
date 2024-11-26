<?php
// Configuración para log
$log_file = '/home/admingalinea/staging.galinea.cl/cron_sync_log.txt';
$log_handle = fopen($log_file, 'a');
if (!$log_handle) {
    error_log('No se pudo abrir el archivo de log: ' . $log_file);
    die('No se pudo abrir el archivo de log.');
}

// Función para registrar mensajes
function log_message($message) {
    global $log_handle;
    fwrite($log_handle, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
}

// Iniciar registro
log_message("Iniciando el script de sincronización por cron.");

// Aumentar límites de recursos
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/home/admingalinea/staging.galinea.cl/error_log_sync_cron.php');

// Cargar WordPress
log_message("Cargando WordPress...");
require_once '/home/admingalinea/staging.galinea.cl/wp-load.php';
log_message("WordPress cargado correctamente.");

// Verificar archivo sync.php
$sync_file = '/home/admingalinea/staging.galinea.cl/wp-content/plugins/WP-Kame-Connect-3.2.7-main/includes/api/sync.php';
if (!file_exists($sync_file)) {
    log_message("ERROR: El archivo sync.php no existe en la ruta especificada.");
    fclose($log_handle);
    exit(1);
}
log_message("Archivo sync.php encontrado.");

// Ejecutar sincronización
log_message("Iniciando sincronización...");
try {
    require_once $sync_file;
    kame_erp_synchronize_inventory();
    log_message("Sincronización completada exitosamente.");
} catch (Exception $e) {
    log_message("ERROR durante la sincronización: " . $e->getMessage());
}

// Finalizar registro
log_message("Finalizando script de sincronización por cron.");
fclose($log_handle);
?>
