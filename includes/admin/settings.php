<?php
function kame_erp_settings_init() {
    // Register settings and callbacks here
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
