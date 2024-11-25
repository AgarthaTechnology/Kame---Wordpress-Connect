<?php
function kame_erp_inventory_settings_init() {
    add_settings_section(
        'kame_erp_inventory_section',
        'Configuración de Inventario de KAME ERP',
        'kame_erp_inventory_section_callback',
        'kame_erp_inventory'
    );

    add_settings_field(
        'kame_erp_warehouses',
        'Bodegas',  // Cambiado de "Almacenes" a "Bodegas"
        'kame_erp_warehouses_callback',
        'kame_erp_inventory',
        'kame_erp_inventory_section'
    );

    add_settings_field(
        'kame_erp_sync_frequency',
        'Frecuencia de Sincronización',
        'kame_erp_sync_frequency_callback',
        'kame_erp_inventory',
        'kame_erp_inventory_section'
    );

    register_setting('kame_erp_inventory', 'kame_erp_warehouses', 'kame_erp_sanitize_warehouses');
    register_setting('kame_erp_inventory', 'kame_erp_sync_frequency');
}

function kame_erp_inventory_section_callback() {
    echo '<p>Configura las opciones de sincronización de inventario.</p>';
}

function kame_erp_warehouses_callback() {
    $warehouses = get_option('kame_erp_warehouses', '');
    $warehouses = is_array($warehouses) ? implode("\n", $warehouses) : $warehouses;
    echo '<p>Ingrese las bodegas con los mismos nombres registrados en KAME ERP, una bodega por línea.</p>';
    echo '<textarea name="kame_erp_warehouses" style="width: 300px; height: 100px;">' . esc_textarea($warehouses) . '</textarea>';
}

function kame_erp_sync_frequency_callback() {
    $frequency = get_option('kame_erp_sync_frequency', 'daily');
    echo '<select name="kame_erp_sync_frequency">
            <option value="hourly" ' . selected($frequency, 'hourly', false) . '>Cada hora</option>
            <option value="twicedaily" ' . selected($frequency, 'twicedaily', false) . '>Dos veces al día</option>
            <option value="daily" ' . selected($frequency, 'daily', false) . '>Diariamente</option>
          </select>';
}

function kame_erp_sanitize_warehouses($input) {
    $warehouses = explode("\n", $input);
    $warehouses = array_filter(array_map('trim', $warehouses));
    return $warehouses;
}

add_action('admin_init', 'kame_erp_inventory_settings_init');

// Agregar pestaña "Bodega Kame" en la edición del producto
function kame_erp_add_product_tab($tabs) {
    $tabs['kame_bodega'] = array(
        'label'    => __('Bodega Kame', 'woocommerce'),
        'target'   => 'kame_bodega_product_data',
        'class'    => array('show_if_simple', 'show_if_variable'),
        'priority' => 21,
    );
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'kame_erp_add_product_tab');

function kame_erp_bodega_product_tab_content() {
    global $post;
    $warehouses = get_option('kame_erp_warehouses', []);
    $selected_warehouses = get_post_meta($post->ID, '_kame_erp_warehouses', true);
    if (!is_array($selected_warehouses)) {
        $selected_warehouses = [];
    }
    echo '<div id="kame_bodega_product_data" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    echo '<p class="form-field">';
    echo '<label for="kame_erp_warehouses">' . __('Bodegas', 'woocommerce') . '</label>';
    foreach ($warehouses as $warehouse) {
        $checked = in_array($warehouse, $selected_warehouses) ? 'checked' : '';
        echo '<input type="checkbox" name="kame_erp_warehouses[]" value="' . esc_attr($warehouse) . '" ' . $checked . '> ' . esc_html($warehouse) . '<br>';
    }
    echo '</p>';
    echo '</div>';
    echo '</div>';
}
add_action('woocommerce_product_data_panels', 'kame_erp_bodega_product_tab_content');

function kame_erp_save_product($post_id) {
    $warehouses = isset($_POST['kame_erp_warehouses']) ? array_map('sanitize_text_field', $_POST['kame_erp_warehouses']) : [];
    update_post_meta($post_id, '_kame_erp_warehouses', $warehouses);
}
add_action('woocommerce_process_product_meta', 'kame_erp_save_product');

// Agregar columna de sincronización en la lista de productos
function kame_erp_add_sync_column($columns) {
    $columns['sync_kame'] = __('Sync Kame', 'woocommerce');
    return $columns;
}
add_filter('manage_edit-product_columns', 'kame_erp_add_sync_column');

function kame_erp_render_sync_column($column, $post_id) {
    if ($column == 'sync_kame') {
        $synced = get_post_meta($post_id, '_kame_erp_synced', true);
        $icon = $synced ? '🟢' : '🔴';
        echo '<span style="font-size: 20px;">' . $icon . '</span>';
    }
}
add_action('manage_product_posts_custom_column', 'kame_erp_render_sync_column', 10, 2);
