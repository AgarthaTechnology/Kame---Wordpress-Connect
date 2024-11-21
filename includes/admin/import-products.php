<?php
function kame_erp_import_products() {
    // Import products logic
}

function kame_erp_add_product_columns($columns) {
    $columns['kame_erp_synced'] = 'KAME ERP';
    return $columns;
}

function kame_erp_product_column_content($column, $post_id) {
    // Content for custom product column
}

function kame_erp_product_column_styles() {
    // Styles for custom product column
}
