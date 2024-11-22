<?php
// Incluye el archivo connection.php para usar las funciones de conexión
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
        'kame_erp_token_status',
        'Disponibilidad del Token',
        'kame_erp_token_status_callback',
        'kame_erp_settings',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_connection_status',
        'Estado de Conexión',
        'kame_erp_connection_status_callback',
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

function kame_erp_token_status_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    $is_available = !empty($access_token);
    $status_text = $is_available ? 'DISPONIBLE' : 'NO DISPONIBLE';
    $status_class = $is_available ? 'status-available' : 'status-unavailable';

    echo '<div class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</div>';
}

function kame_erp_connection_status_callback() {
    $is_connected = kame_erp_check_connection();
    $status_text = $is_connected ? 'ONLINE' : 'OFFLINE';
    $status_class = $is_connected ? 'status-online' : 'status-offline';

    echo '<div class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</div>';
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

// Estilos para los indicadores de estado
add_action('admin_head', function () {
    echo '<style>
        .status-online {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-offline {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-available {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-unavailable {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>';
});

// Inicializar la configuración
add_action('admin_init', 'kame_erp_settings_init');

