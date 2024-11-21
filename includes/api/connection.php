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
    $token_expiration = get_option('kame_erp_token_expiration', 0);
    if (time() > $token_expiration) {
        fetch_and_store_kame_erp_access_token();
    }
}

function kame_erp_access_token_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    echo '<input type="text" name="kame_erp_access_token" value="' . esc_attr($access_token) . '" style="width: 100%;" readonly>';
}

function update_kame_erp_access_token($token_response) {
    // Update access token logic
}

function fetch_and_store_kame_erp_access_token() {
    $client_id = get_option('kame_erp_client_id', '');
    $client_secret = get_option('kame_erp_client_secret', '');
    $usuario_kame = get_option('kame_erp_usuario_kame', '');

    $response = wp_remote_post('https://api.kameerp.com/oauth/token', array(
        'body' => array(
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $usuario
