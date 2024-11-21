<?php
function kame_erp_inventory_settings_init() {
    // Register inventory settings here
}

function kame_erp_inventory_section_callback() {
    echo '<p>Configura las opciones de sincronización de inventario.</p>';
}

function kame_erp_warehouses_callback() {
    // Callback for warehouses
}

function kame_erp_sync_frequency_callback() {
    // Callback for sync frequency
}

function kame_erp_sanitize_warehouses($input) {
    // Sanitize warehouses input
}

function kame_erp_add_warehouse_field() {
    // Add custom warehouse field
}

function kame_erp_save_warehouse_field($post_id) {
    $warehouse = isset($_POST['_kame_erp_warehouse']) ? sanitize_text_field($_POST['_kame_erp_warehouse']) : '';
    update_post_meta($post_id, '_kame_erp_warehouse', $warehouse);
}
