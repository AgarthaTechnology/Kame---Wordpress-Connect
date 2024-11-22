<?php
function kame_erp_get_access_token() {
    return get_option('kame_erp_access_token', '');
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
    if (!empty($token_response->access_token)) {
        update_option('kame_erp_access_token', $token_response->access_token);
        update_option('kame_erp_token_expiration', time() + $token_response->expires_in);
    }
}

function fetch_and_store_kame_erp_access_token() {
    $client_id = get_option('kame_erp_client_id', '');
    $client_secret = get_option('kame_erp_client_secret', '');
    $audience = 'https://api.kameone.cl/api';
    $grant_type = 'client_credentials';

    error_log('Fetching KAME ERP access token...');
    error_log('Client ID: ' . $client_id);
    error_log('Client Secret: ' . str_repeat('*', strlen($client_secret) - 4) . substr($client_secret, -4));

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.kameone.cl/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'audience' => $audience,
            'grant_type' => $grant_type
        )),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        error_log('Error en cURL: ' . curl_error($curl));
        curl_close($curl);
        return false;
    }

    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code !== 200) {
        error_log('Error en la respuesta de la API: Código de estado ' . $http_code);
        error_log('Respuesta completa: ' . $response);
        curl_close($curl);
        return false;
    }

    curl_close($curl);

    $data = json_decode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error al decodificar JSON: ' . json_last_error_msg());
        error_log('Cuerpo de la respuesta: ' . $response);
        return false;
    }

    error_log('Respuesta completa de la API: ' . $response);

    if (!empty($data->access_token)) {
        update_kame_erp_access_token($data);
        error_log('Token obtenido exitosamente.');
        wp_send_json_success('Token obtenido exitosamente.');
    }

    error_log('La respuesta de la API no contiene un token de acceso. Respuesta: ' . $response);
    wp_send_json_error('No se pudo obtener el token.');
}

// Registrar el hook AJAX
add_action('wp_ajax_fetch_and_store_kame_erp_access_token', 'fetch_and_store_kame_erp_access_token');
