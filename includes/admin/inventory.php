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
        'Almacenes',
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
    $warehouses = get_option('kame_erp_warehouses', []);
    echo '<textarea name="kame_erp_warehouses" style="width: 100%;">' . esc_textarea(json_encode($warehouses)) . '</textarea>';
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
    $warehouses = json_decode($input, true);
    return is_array($warehouses) ? $warehouses : [];
}

add_action('admin_init', 'kame_erp_inventory_settings_init');
