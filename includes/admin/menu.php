<?php
function kame_erp_menu() {
    add_menu_page(
        'Configuración de KAME ERP', 
        'KAME ERP', 
        'manage_options', 
        'kame_erp_settings', 
        'kame_erp_config_page', 
        'dashicons-admin-generic', 
        56 
    );

    add_submenu_page(
        'kame_erp_settings', 
        'Funciones de KAME ERP', 
        'Funciones', 
        'manage_options', 
        'kame_erp_functions', 
        'kame_erp_functions_page' 
    );

    add_submenu_page(
        'kame_erp_settings',
        'Inventario de KAME ERP',
        'Inventario',
        'manage_options',
        'kame_erp_inventory',
        'kame_erp_inventory_page'
    );

    add_submenu_page(
        'kame_erp_settings',
        'Importar/Exportar Kame-Woocommerce',
        'Importar Productos',
        'manage_options',
        'kame_erp_import_products',
        'kame_erp_import_export_page' // Solo referencia a la función definida en import-products.php
    );

    add_submenu_page(
        'kame_erp_settings',
        'Créditos',
        'Créditos',
        'manage_options',
        'kame_erp_credits',
        'kame_erp_credits_page'
    );
}

add_action('admin_menu', 'kame_erp_menu');
