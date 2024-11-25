<?php
// Incluye WordPress para acceder a sus funciones
require_once('/path/to/your/wp-load.php'); // Ajusta esta ruta

// Incluye el archivo de sincronización
require_once plugin_dir_path(__FILE__) . 'includes/api/sync.php';

// Ejecuta la sincronización
kame_erp_synchronize_inventory();
