<?php

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

// Función para mostrar los logs de sincronización
function display_sync_log() {
    // Título de la página
    echo '<h1>Registro de Sincronización</h1>';

    // Contenedor para los filtros
    echo '<div>';
    echo '<label for="filter_date">Fecha:</label>';
    echo '<input type="date" id="filter_date" name="filter_date">';
    
    echo '<label for="filter_type">Tipo:</label>';
    echo '<select id="filter_type" name="filter_type">';
    echo '<option value="all">Todos</option>';
    echo '<option value="error">Error</option>';
    echo '<option value="success">Éxito</option>';
    echo '</select>';
    
    echo '<button id="filter_button" onclick="filterLogs()">Filtrar</button>';
    echo '</div>';

    // Contenedor para los logs
    echo '<div style="overflow: auto; height: 300px; width: 100%;">';
    echo '<table border="1">';
    echo '<thead><tr><th>Fecha</th><th>Tipo</th><th>Mensaje</th></tr></thead>';
    echo '<tbody id="log_table">';

    // Obtener los logs (aquí debes implementar la lógica para obtener los logs de sync.php)
    $logs = get_sync_logs(); // Esta función debe ser implementada para obtener los logs

    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . esc_html($log['date']) . '</td>';
        echo '<td>' . esc_html($log['type']) . '</td>';
        echo '<td>' . esc_html($log['message']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

function get_sync_logs() {
    // Implementa la lógica para obtener los logs de sync.php
    // Aquí se debe leer el archivo de logs y devolver un array con los logs
    // Ejemplo de log: ['date' => '2024-11-25', 'type' => 'success', 'message' => 'Sincronización exitosa']

    // Código de ejemplo (debes reemplazar esto con la lógica real)
    return [
        ['date' => '2024-11-25', 'type' => 'success', 'message' => 'Sincronización exitosa'],
        ['date' => '2024-11-25', 'type' => 'error', 'message' => 'Error al sincronizar']
    ];
}

add_action('admin_menu', function() {
    add_menu_page('Registro de Sincronización', 'Registro de Sincronización', 'manage_options', 'sync-log', 'display_sync_log');
});

function filterLogs() {
    // Implementa la lógica para filtrar los logs según los filtros seleccionados
    // Este código debe ser implementado en JavaScript
}

?>
