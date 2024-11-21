<?php
function kame_erp_import_products_settings_init() {
    add_settings_section(
        'kame_erp_import_products_section',
        'Configuración de Importación de Productos',
        'kame_erp_import_products_section_callback',
        'kame_erp_import_products'
    );

    add_settings_field(
        'kame_erp_import_frequency',
        'Frecuencia de Importación',
        'kame_erp_import_frequency_callback',
        'kame_erp_import_products',
        'kame_erp_import_products_section'
    );

    register_setting('kame_erp_import_products', 'kame_erp_import_frequency');
}

function kame_erp_import_products_section_callback() {
    echo '<p>Configura las opciones de importación de productos.</p>';
}

function kame_erp_import_frequency_callback() {
    $frequency = get_option('kame_erp_import_frequency', 'daily');
    echo '<select name="kame_erp_import_frequency">
            <option value="hourly" ' . selected($frequency, 'hourly', false) . '>Cada hora</option>
            <option value="twicedaily" ' . selected($frequency, 'twicedaily', false) . '>Dos veces al día</option>
            <option value="daily" ' . selected($frequency, 'daily', false) . '>Diariamente</option>
          </select>';
}

add_action('admin_init', 'kame_erp_import_products_settings_init');
