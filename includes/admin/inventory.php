<?php
function kame_erp_inventory_settings_init() {
    add_settings_section(
        'kame_erp_inventory_section',
        'Configuración de Inventario de KAME ERP',
        'kame_erp_inventory_section_callback',
        'kame_erp_inventory'
    );

    add_settings_field(
        'kame_erp_warehouses',
        'Bodegas',  // Cambiado de "Almacenes" a "Bodegas"
        'kame_erp_warehouses_callback',
        'kame_erp_inventory',
        'kame_erp_inventory_section'
    );

    add_settings_field(
        'kame_erp_sync_frequency',
        'Frecuencia de Sincronización',
        'kame_erp_sync_frequency_callback',
        'kame_erp_inventory',
        'kame_erp_inventory_section'
    );

    register_setting('kame_erp_inventory', 'kame_erp_warehouses', 'kame_erp_sanitize_warehouses');
    register_setting('kame_erp_inventory', 'kame_erp_sync_frequency');
}

function kame_erp_inventory_section_callback() {
    echo '<p>Configura las opciones de sincronización de inventario.</p>';
}

function kame_erp_warehouses_callback() {
    $warehouses = get_option('kame_erp_warehouses', '');
    $warehouses = is_array($warehouses) ? implode("\n", $warehouses) : $warehouses;
    echo '<p>Ingrese las bodegas con los mismos nombres registrados en KAME ERP, una bodega por línea.</p>';
    echo '<textarea name="kame_erp_warehouses" style="width: 300px; height: 100px;">' . esc_textarea($warehouses) . '</textarea>';
}

function kame_erp_sync_frequency_callback() {
    $frequency = get_option('kame_erp_sync_frequency', 'daily');
    echo '<select name="kame_erp_sync_frequency">
            <option value="hourly" ' . selected($frequency, 'hourly', false) . '>Cada hora</option>
            <option value="twicedaily" ' . selected($frequency, 'twicedaily', false) . '>Dos veces al día</option>
            <option value="daily" ' . selected($frequency, 'daily', false) . '>Diariamente</option>
          </select>';
}

function kame_erp_sanitize_warehouses($input) {
    $warehouses = explode("\n", $input);
    $warehouses = array_filter(array_map('trim', $warehouses));
    return $warehouses;
}

add_action('admin_init', 'kame_erp_inventory_settings_init');
