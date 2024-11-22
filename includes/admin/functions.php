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
        function() { kame_erp_checkbox_field_callback('kame_erp_enable_integration'); },
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    add_settings_field(
        'kame_erp_enable_sync',
        'Habilitar Sincronización',
        function() { kame_erp_checkbox_field_callback('kame_erp_enable_sync'); },
        'kame_erp_functions',
        'kame_erp_functions_section'
    );

    register_setting('kame_erp_functions', 'kame_erp_enable_integration');
    register_setting('kame_erp_functions', 'kame_erp_enable_sync');
}

function kame_erp_functions_section_callback() {
    echo '<p>Puedes activar o desactivar las funciones integradas del plugin según tus necesidades.</p>';
}

function kame_erp_checkbox_field_callback($option_name) {
    $checked = get_option($option_name) ? 'checked' : '';
    echo '<input type="checkbox" name="' . esc_attr($option_name) . '" ' . $checked . '>';
}

add_action('admin_init', 'kame_erp_functions_settings_init');
