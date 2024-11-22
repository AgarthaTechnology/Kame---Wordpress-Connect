<?php
function fetch_and_store_kame_erp_access_token() {
    $client_id = get_option('kame_erp_client_id', '');
    $client_secret = get_option('kame_erp_client_secret', '');
    $audience = 'https://api.kameone.cl/api';
    $grant_type = 'client_credentials';

    if (empty($client_id) || empty($client_secret)) {
        error_log('Client ID o Client Secret no configurados.');
        wp_send_json_error('Client ID o Client Secret no configurados.');
        return;
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
        wp_send_json_error('Error al conectar con la API.');
        return;
    }

    if ($http_code !== 200) {
        error_log('Error HTTP: ' . $http_code);
        error_log('Respuesta de la API: ' . $response);
        curl_close($curl);
        wp_send_json_error('Error al obtener el token.');
        return;
    }

    curl_close($curl);
    $data = json_decode($response);

    if (!empty($data->access_token)) {
        update_option('kame_erp_access_token', $data->access_token);
        update_option('kame_erp_token_expiration', time() + $data->expires_in);
        wp_send_json_success('Token obtenido exitosamente.');
        return;
    }

    error_log('La respuesta no contiene un token válido.');
    wp_send_json_error('No se pudo obtener el token.');
}

// Registrar el hook AJAX
add_action('wp_ajax_fetch_and_store_kame_erp_access_token', 'fetch_and_store_kame_erp_access_token');
