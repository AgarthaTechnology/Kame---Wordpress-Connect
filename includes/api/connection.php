<?php
// Función para obtener y almacenar el token de acceso
function fetch_and_store_kame_erp_access_token() {
    $client_id = get_option('kame_erp_client_id', '');
    $client_secret = get_option('kame_erp_client_secret', '');
    $audience = 'https://api.kameone.cl/api';
    $grant_type = 'client_credentials';

    if (empty($client_id) || empty($client_secret)) {
        error_log('Client ID o Client Secret no configurados.');
        return array('success' => false, 'message' => 'Client ID o Client Secret no configurados.');
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.kameone.cl/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_POSTFIELDS => json_encode(array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'audience' => $audience,
            'grant_type' => $grant_type,
        )),
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        error_log('Error en cURL: ' . curl_error($curl));
        curl_close($curl);
        return array('success' => false, 'message' => 'Error al conectar con la API.');
    }

    if ($http_code !== 200) {
        error_log('Error HTTP: ' . $http_code);
        error_log('Respuesta de la API: ' . $response);
        curl_close($curl);
        return array('success' => false, 'message' => 'Error al obtener el token.');
    }

    curl_close($curl);
    $data = json_decode($response);

    if (!empty($data->access_token)) {
        update_option('kame_erp_access_token', $data->access_token);
        update_option('kame_erp_token_expiration', time() + $data->expires_in);
        return array('success' => true, 'message' => 'Token obtenido exitosamente.');
    }

    error_log('La respuesta no contiene un token válido.');
    return array('success' => false, 'message' => 'No se pudo obtener el token.');
}

// Función para verificar la conexión
function kame_erp_check_connection() {
    $access_token = get_option('kame_erp_access_token', '');
    if (empty($access_token)) {
        return false;
    }

    $response = wp_remote_get('https://api.kameone.cl/api/status', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    return $status_code === 200;
}

// Agregar estado al Admin Bar
add_action('admin_bar_menu', function ($admin_bar) {
    $is_connected = kame_erp_check_connection();
    $status_text = $is_connected ? 'Online' : 'Offline';
    $status_class = $is_connected ? 'status-online' : 'status-offline';

    $admin_bar->add_node(array(
        'id'    => 'kame_erp_connection_status',
        'title' => 'KAME ERP: <span class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>',
        'meta'  => array('title' => 'Estado de conexión con KAME ERP'),
    ));
});

// Registrar el hook AJAX para obtener el token
add_action('wp_ajax_fetch_and_store_kame_erp_access_token', function() {
    $result = fetch_and_store_kame_erp_access_token();
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
});
?>
