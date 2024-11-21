<?php
function custom_checkout_field($checkout) {
    // Add custom checkout field
}

function mostrar_campos_factura() {
    // Display invoice fields in footer
}

function validar_campos_factura() {
    // Validate invoice fields
}

function guardar_campos_factura($order_id) {
    // Save invoice fields in order meta
}

function mostrar_campos_factura_admin($order) {
    // Display invoice fields in admin order
}

add_action('wp_footer', 'mostrar_campos_factura');
add_action('woocommerce_checkout_process', 'validar_campos_factura');
add_action('woocommerce_checkout_update_order_meta', 'guardar_campos_factura');
add_action('woocommerce_admin_order_data_after_billing_address', 'mostrar_campos_factura_admin', 10, 1);
