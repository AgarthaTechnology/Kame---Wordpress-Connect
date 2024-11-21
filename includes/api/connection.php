<?php
function kame_erp_get_access_token() {
    // Get access token logic
}

function kame_erp_check_connection() {
    $access_token = kame_erp_get_access_token();
    return $access_token ? true : false;
}

function kame_erp_enviar_datos_venta($order_id) {
    // Send sales data to API
}

function kame_erp_enviar_a_api($datos_venta) {
    // Send data to API logic
}

function kame_erp_check_and_refresh_token() {
    // Check and refresh token logic
}

function kame_erp_access_token_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    echo '<input type="text" name="kame_erp_access_token" value="' . esc_attr($access_token) . '" style="width: 100%;" readonly>';
}

function update_kame_erp_access_token($token_response) {
    // Update access token logic
}

function fetch_and_store_kame_erp_access_token() {
    // Fetch and store access token logic
}
