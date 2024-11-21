<?php
function kame_erp_import_products() {
    // Lógica para importar productos
}

function kame_erp_add_product_columns($columns) {
    $columns['kame_erp_synced'] = 'KAME ERP';
    return $columns;
}

function kame_erp_product_column_content($column, $post_id) {
    // Contenido para la columna personalizada de productos
}

function kame_erp_product_column_styles() {
    // Estilos para la columna personalizada de productos
}
