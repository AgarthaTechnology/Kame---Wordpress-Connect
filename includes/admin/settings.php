<?php
// Incluir el archivo connection.php para usar la función fetch_and_store_kame_erp_access_token
require_once plugin_dir_path(__FILE__) . '../api/connection.php';

function kame_erp_settings_init() {
    // Registrar una sección de configuración
    add_settings_section(
        'kame_erp_section',
        'Configuración de KAME ERP',
        'kame_erp_section_callback',
        'kame_erp_settings'
    );

    // Registrar los campos de configuración
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

    // Registrar las opciones
    register_setting('kame_erp_settings', 'kame_erp_client_id', 'sanitize_text_field');
    register_setting('kame_erp_settings', 'kame_erp_client_secret', 'sanitize_text_field');
    register_setting('kame_erp_settings', 'kame_erp_access_token', 'sanitize_text_field');
    register_setting('kame_erp_settings', 'kame_erp_token_expiration', 'intval');
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

function kame_erp_token_info_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    $token_expiration = (int) get_option('kame_erp_token_expiration', 0);
    $expiration_date = $token_expiration ? date('Y-m-d H:i:s', $token_expiration) : 'No configurado';

    echo '<p>Access Token: <input type="text" value="' . esc_attr($access_token) . '" style="width: 100%;" readonly></p>';
    echo '<p>Token Expiration: <input type="text" value="' . esc_attr($expiration_date) . '" style="width: 100%;" readonly></p>';
}

function kame_erp_manual_token_button_callback() {
    $client_id = get_option('kame_erp_client_id', '');
    $client_secret = get_option('kame_erp_client_secret', '');

    if (empty($client_id) || empty($client_secret)) {
        echo '<p style="color: red;">Por favor, introduce el Client ID y el Client Secret antes de obtener el token.</p>';
        return;
    }

    echo '<button id="kame_erp_manual_token_button">Obtener Token Manualmente</button>';
}

// JavaScript para manejar el botón "Obtener Token Manualmente"
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

// Inicializar la configuración
add_action('admin_init', 'kame_erp_settings_init');
