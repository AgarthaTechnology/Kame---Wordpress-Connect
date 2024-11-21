<?php
function kame_erp_menu() {
    add_menu_page(
        'KAME ERP Settings', // Título de la página
        'KAME ERP', // Título del menú
        'manage_options', // Capacidad requerida
        'kame_erp_settings', // Slug del menú
        'kame_erp_config_page', // Función para mostrar el contenido
        'dashicons-admin-generic', // Icono del menú
        100 // Posición
    );

    add_submenu_page(
        'kame_erp_settings', // Slug del menú principal
        'KAME ERP Functions', // Título de la página
        'Functions', // Título del submenú
        'manage_options', // Capacidad requerida
        'kame_erp_functions', // Slug del submenú
        'kame_erp_functions_page' // Función para mostrar el contenido
    );

    add_submenu_page(
        'kame_erp_settings',
        'KAME ERP Inventory',
        'Inventory',
        'manage_options',
        'kame_erp_inventory',
        'kame_erp_inventory_page'
    );

    add_submenu_page(
        'kame_erp_settings',
        'Import Products',
        'Import Products',
        'manage_options',
        'kame_erp_import_products',
        'kame_erp_import_products_page'
    );

    add_submenu_page(
        'kame_erp_settings',
        'Credits',
        'Credits',
        'manage_options',
        'kame_erp_credits',
        'kame_erp_credits_page'
    );
}

function kame_erp_config_page() {
    echo '<h1>KAME ERP Settings</h1>';
    // Agrega el contenido de la página de configuración aquí
}

function kame_erp_functions_page() {
    echo '<h1>KAME ERP Functions</h1>';
    // Agrega el contenido de la página de funciones aquí
}

function kame_erp_inventory_page() {
    echo '<h1>KAME ERP Inventory</h1>';
    // Agrega el contenido de la página de inventario aquí
}

function kame_erp_import_products_page() {
    echo '<h1>Import Products</h1>';
    // Agrega el contenido de la página de importación de productos aquí
}

function kame_erp_credits_page() {
    echo '<h1>Credits</h1>';
    // Agrega el contenido de la página de créditos aquí
}

add_action('admin_menu', 'kame_erp_menu');
