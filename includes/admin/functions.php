<?php
function kame_erp_functions_settings_init() {
    add_settings_section(
        'kame_erp_functions_section',
        'Configuración de Funciones de KAME ERP',
        'kame_erp_functions_section_callback',
        'kame_erp_functions'
    );

    add_settings_field(
        'kame_erp_enable_integration',
        'Habilitar Integración',
        function() { kame_erp_checkbox_field_callback('kame_erp_enable_integration'); },
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    add_settings_field(
        'kame_erp_enable_sync',
        'Habilitar Sincronización',
        function() { kame_erp_checkbox_field_callback('kame_erp_enable_sync'); },
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    register_setting('kame_erp_functions', 'kame_erp_enable_integration');
    register_setting('kame_erp_functions', 'kame_erp_enable_sync');
}

function kame_erp_functions_section_callback() {
    echo '<p>Puedes activar o desactivar las funciones integradas del plugin según tus necesidades.</p>';
}

function kame_erp_checkbox_field_callback($option_name) {
    $checked = get_option($option_name) ? 'checked' : '';
    echo '<input type="checkbox" name="' . esc_attr($option_name) . '" ' . $checked . '>';
}

add_action('admin_init', 'kame_erp_functions_settings_init');

// Nueva función para sincronizar inventario desde KAME ERP
function sync_inventory_from_kame() {
    $token = get_option('kame_erp_access_token');
    $warehouses = get_option('kame_erp_warehouses', []);
    foreach ($warehouses as $warehouse) {
        // Lógica para sincronizar cada bodega
        $stock = get_kame_stock($warehouse, $token);
        // Actualizar inventario en WooCommerce
    }
}

// Nueva función para importar productos desde KAME ERP
function import_products_from_kame() {
    $token = get_option('kame_erp_access_token');
    $products = get_kame_products($token);
    foreach ($products as $product) {
        $stock_info = get_kame_stock($product['SKU'], $token);
        $product_data = prepare_woocommerce_product_data($product, $stock_info);
        create_woocommerce_product($product_data);
    }
}

// Funciones auxiliares
function get_kame_products($token) {
    $url = 'https://api.kameone.cl/api/Maestro/getListArticulo';
    $headers = ['Authorization' => 'Bearer ' . $token];
    $response = api_request($url, $headers);
    return json_decode($response, true);
}

function get_kame_stock($sku, $token) {
    $url = 'https://api.kameone.cl/api/Inventario/getStockArticulo/' . $sku;
    $headers = ['Authorization' => 'Bearer ' . $token];
    $response = api_request($url, $headers);
    return json_decode($response, true);
}

function prepare_woocommerce_product_data($kame_product, $stock_info) {
    $product_data = [
        'name' => $kame_product['Descripcion'],
        'type' => 'simple',
        'regular_price' => $kame_product['PrecioVentaNeto'],
        'description' => 'Descripción no disponible',
        'short_description' => 'Descripción no disponible',
        'sku' => $kame_product['SKU'],
        'stock_status' => $stock_info['saldo'] > 0 ? 'instock' : 'outofstock',
        'stock_quantity' => $stock_info['saldo'],
        'categories' => [/* Map categories as needed */],
        'images' => [],
        'attributes' => [],
    ];
    return $product_data;
}

function create_woocommerce_product($product_data) {
    $product = new WC_Product();
    $product->set_props($product_data);
    $product->save();
}

function api_request($url, $headers, $body = null, $method = 'GET') {
    $response = wp_remote_request($url, [
        'method' => $method,
        'headers' => $headers,
        'body' => $body,
    ]);
    return wp_remote_retrieve_body($response);
}

function log_import_error($message) {
    error_log($message);
}

function get_import_errors() {
    return get_option('kame_erp_import_errors', []);
}
