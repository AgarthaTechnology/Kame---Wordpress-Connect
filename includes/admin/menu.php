<?php
function kame_erp_menu() {
    add_menu_page(
        'Configuración de KAME ERP', // Título de la página
        'KAME ERP', // Título del menú
        'manage_options', // Capacidad requerida
        'kame_erp_settings', // Slug del menú
        'kame_erp_config_page', // Función para mostrar el contenido
        'dashicons-admin-generic', // Icono del menú
        100 // Posición
    );

    add_submenu_page(
        'kame_erp_settings', // Slug del menú principal
        'Funciones de KAME ERP', // Título de la página
        'Funciones', // Título del submenú
        'manage_options', // Capacidad requerida
        'kame_erp_functions', // Slug del submenú
        'kame_erp_functions_page' // Función para mostrar el contenido
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
        'Importar Productos',
        'Importar Productos',
        'manage_options',
        'kame_erp_import_products',
        'kame_erp_import_products_page'
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

function kame_erp_config_page() {
    echo '<h1>Configuración de KAME ERP</h1>';
    echo '<p>Contenido de la página de configuración.</p>';
}

function kame_erp_functions_page() {
    echo '<h1>Funciones de KAME ERP</h1>';
    echo '<p>Contenido de la página de funciones.</p>';
}

function kame_erp_inventory_page() {
    echo '<h1>Inventario de KAME ERP</h1>';
    echo '<p>Contenido de la página de inventario.</p>';
}

function kame_erp_import_products_page() {
    echo '<h1>Importar Productos</h1>';
    echo '<p>Contenido de la página de importación de productos.</p>';
}

function kame_erp_credits_page() {
    echo '<h1>Créditos</h1>';
    echo '<p>Contenido de la página de créditos.</p>';
}

add_action('admin_menu', 'kame_erp_menu');
