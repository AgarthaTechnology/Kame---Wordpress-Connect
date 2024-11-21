<?php
function kame_erp_settings_init() {
    add_settings_section(
        'kame_erp_section',
        'Configuración de KAME ERP',
        'kame_erp_section_callback',
        'kame_erp_settings'
    );

    add_settings_field(
        'kame_erp_client_id',
        'Client ID',
        'kame_erp_client_id_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_client_secret',
        'Client Secret',
        'kame_erp_client_secret_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_usuario_kame',
        'Usuario KAME',
        'kame_erp_usuario_kame_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_token_info',
        'Información del Token',
        'kame_erp_token_info_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_manual_token_button',
        'Obtener Token Manualmente',
        'kame_erp_manual_token_button_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    register_setting('kame_erp_settings', 'kame_erp_client_id');
    register_setting('kame_erp_settings', 'kame_erp_client_secret');
    register_setting('kame_erp_settings', 'kame_erp_usuario_kame');
    register_setting('kame_erp_settings', 'kame_erp_access_token');
    register_setting('kame_erp_settings', 'kame_erp_token_expiration');
}

function kame_erp_section_callback() {
    echo '<p>Introduce las credenciales para conectar con la API de KAME ERP. Estos datos son sensibles, asegúrate de ingresarlos correctamente.</p>';
}

function kame_erp_client_id_callback() {
    $client_id = get_option('kame_erp_client_id', '');
    echo '<input type="text" name="kame_erp_client_id" value="' . esc_attr($client_id) . '" style="width: 100%;" required>';
}

function kame_erp_client_secret_callback() {
    $client_secret = get_option('kame_erp_client_secret', '');
    echo '<input type="password" name="kame_erp_client_secret" value="' . esc_attr($client_secret) . '" style="width: 100%;" required>';
}

function kame_erp_usuario_kame_callback() {
    $usuario_kame = get_option('kame_erp_usuario_kame', '');
    echo '<input type="text" name="kame_erp_usuario_kame" value="' . esc_attr($usuario_kame) . '" style="width: 100%;" required>';
}

function kame_erp_token_info_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    $token_expiration = get_option('kame_erp_token_expiration', 0);
    $expiration_date = date('Y-m-d H:i:s', $token_expiration);
    echo '<p>Access Token: <input type="text" value="' . esc_attr($access_token) . '" style="width: 100%;" readonly></p>';
    echo '<p>Token Expiration: <input type="text" value="' . esc_attr($expiration_date) . '" style="width: 100%;" readonly></p>';
}

function kame_erp_manual_token_button_callback() {
    echo '<button id="kame_erp_manual_token_button">Obtener Token Manualmente</button>';
}

// JavaScript para manejar el clic del botón
add_action('admin_footer', function () {
    echo '<script type="text/javascript">
        document.getElementById("kame_erp_manual_token_button").onclick = function() {
            fetch("' . admin_url('admin-ajax.php') . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=fetch_and_store_kame_erp_access_token"
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Token obtenido exitosamente!");
                    location.reload();
                } else {
                    alert("Error al obtener el token.");
                }
            });
        };
    </script>';
});

add_action('admin_init', 'kame_erp_settings_init');

// Función para obtener y almacenar el token de acceso de KAME ERP
function fetch_and_store_kame_erp_access_token() {
    $client_id = get_option('kame_erp_client_id');
    $client_secret = get_option('kame_erp_client_secret');
    $usuario_kame = get_option('kame_erp_usuario_kame');

    // URL de la API de KAME ERP para obtener el token
    $url = 'https://api.kameerp.com/oauth/token';

    // Configuración de la solicitud
    $response = wp_remote_post($url, array(
        'body' => array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $usuario_kame,
            'grant_type' => 'client_credentials'
        )
    ));

    // Manejar la respuesta
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Error al hacer la solicitud a la API.'));
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['access_token'])) {
            update_option('kame_erp_access_token', $data['access_token']);
            update_option('kame_erp_token_expiration', time() + $data['expires_in']);
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Error en la respuesta de la API.'));
        }
    }
}

// Registrar la función AJAX
add_action('wp_ajax_fetch_and_store_kame_erp_access_token', 'fetch_and_store_kame_erp_access_token');
