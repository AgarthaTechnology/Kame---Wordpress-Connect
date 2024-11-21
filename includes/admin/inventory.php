<?php
function kame_erp_inventory_settings_init() {
    // Registrar configuraciones de inventario aquí
}

function kame_erp_inventory_section_callback() {
    echo '<p>Configura las opciones de sincronización de inventario.</p>';
}

function kame_erp_warehouses_callback() {
    // Callback para almacenes
}

function kame_erp_sync_frequency_callback() {
    // Callback para la frecuencia de sincronización
}

function kame_erp_sanitize_warehouses($input) {
    // Sanitizar entrada de almacenes
}

function kame_erp_add_warehouse_field() {
    // Agregar campo personalizado de almacenes
}

function kame_erp_save_warehouse_field($post_id) {
    $warehouse = isset($_POST['_kame_erp_warehouse']) ? sanitize_text_field($_POST['_kame_erp_warehouse']) : '';
    update_post_meta($post_id, '_kame_erp_warehouse', $warehouse);
}
