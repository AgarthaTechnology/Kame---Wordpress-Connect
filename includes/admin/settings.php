<?php
function kame_erp_settings_init() {
    // Register settings and callbacks here

    add_settings_section(
        'kame_erp_section',
        __('Configuración de KAME ERP', 'kame-erp'),
        'kame_erp_section_callback',
        'kame_erp_settings'
    );

    add_settings_field(
        'kame_erp_client_id',
        __('Client ID', 'kame-erp'),
        'kame_erp_client_id_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_client_secret',
        __('Client Secret', 'kame-erp'),
        'kame_erp_client_secret_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_usuario_kame',
        __('Usuario KAME', 'kame-erp'),
        'kame_erp_usuario_kame_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_token_info',
        __('Token Information', 'kame-erp'),
        'kame_erp_token_info_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_manual_token_button',
        __('Obtener Token Manualmente', 'kame-erp'),
        'kame_erp_manual_token_button_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    register_setting('kame_erp_settings', 'kame_erp_client_id');
    register_setting('kame_erp_settings', 'kame_erp_client_secret');
    register_setting('kame_erp_settings', 'kame_erp_usuario_kame');
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
