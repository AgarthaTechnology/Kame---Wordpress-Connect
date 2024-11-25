<?php
function kame_erp_menu() {
    add_menu_page(
        'Configuración de KAME ERP', // Título de la página
        'KAME ERP', // Título del menú
        'manage_options', // Capacidad requerida
        'kame_erp_settings', // Slug del menú
        'kame_erp_config_page', // Función para mostrar el contenido
        'dashicons-admin-generic', // Icono del menú
        56 // Posición (después de WooCommerce)
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
        'Créditos',
        'Créditos',
        'manage_options',
        'kame_erp_credits',
        'kame_erp_credits_page'
    );

    add_submenu_page(
        'kame_erp_settings',
        'Instrucciones Cron Job',
        'Instrucciones Cron Job',
        'manage_options',
        'kame_erp_cron_instructions',
        'kame_erp_cron_instructions'
    );
}

function kame_erp_config_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de KAME ERP</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_settings');
            do_settings_sections('kame_erp_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function kame_erp_functions_page() {
    ?>
    <div class="wrap">
        <h1>Funciones de KAME ERP</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_functions');
            do_settings_sections('kame_erp_functions');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function kame_erp_inventory_page() {
    ?>
    <div class="wrap">
        <h1>Inventario de KAME ERP</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_inventory');
            do_settings_sections('kame_erp_inventory');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function kame_erp_credits_page() {
    ?>
    <div class="wrap">
        <h1>Créditos</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_credits');
            do_settings_sections('kame_erp_credits');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function kame_erp_cron_instructions() {
    ?>
    <div class="wrap">
        <h1>Instrucciones para Configurar el Cron Job</h1>
        <p>Sigue estos pasos para configurar la sincronización automática cada 10 minutos usando un cron job en cPanel:</p>
        <ol>
            <li>Accede a tu cuenta de cPanel.</li>
            <li>Busca la sección "Cron Jobs" y haz clic en ella.</li>
            <li>Añade un nuevo cron job con la siguiente configuración:</li>
            <ul>
                <li><strong>Comando:</strong> <code>php /path/to/your/wp-content/plugins/WP-Kame-Connect-3.2.7/sync.php</code></li>
                <li><strong>Configuración de tiempo:</strong> <code>*/10 * * * *</code> (esto ejecutará el script cada 10 minutos)</li>
            </ul>
        </ol>
    </div>
    <?php
}

add_action('admin_menu', 'kame_erp_menu');
