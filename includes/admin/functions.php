<?php
function kame_erp_functions_settings_init() {
    add_settings_section(
        'kame_erp_functions_section',
        'Configuración de Funciones de KAME ERP',
        'kame_erp_functions_section_callback',
        'kame_erp_functions'
    );

    add_settings_field(
        'kame_erp_enable_integration',
        'Habilitar Integración',
        'kame_erp_enable_integration_callback',
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    add_settings_field(
        'kame_erp_enable_sync',
        'Habilitar Sincronización',
        'kame_erp_enable_sync_callback',
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    register_setting('kame_erp_functions', 'kame_erp_enable_integration');
    register_setting('kame_erp_functions', 'kame_erp_enable_sync');
}

function kame_erp_functions_section_callback() {
    echo '<p>Puedes activar o desactivar las funciones integradas del plugin según tus necesidades.</p>';
}

function kame_erp_enable_integration_callback() {
    $checked = get_option('kame_erp_enable_integration') ? 'checked' : '';
    echo '<input type="checkbox" name="kame_erp_enable_integration" ' . $checked . '>';
}

function kame_erp_enable_sync_callback() {
    $checked = get_option('kame_erp_enable_sync') ? 'checked' : '';
    echo '<input type="checkbox" name="kame_erp_enable_sync" ' . $checked . '>';
}

add_action('admin_init', 'kame_erp_functions_settings_init');
