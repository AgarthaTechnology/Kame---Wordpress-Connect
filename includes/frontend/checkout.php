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

function enviar_pedido_a_kame_erp($order_id) {
    // Obtener el token de acceso
    $access_token = get_option('kame_erp_access_token');

    if (!$access_token) {
        error_log('Token de acceso no disponible.');
        return;
    }

    // Obtener los datos del pedido
    $order = wc_get_order($order_id);

    $data = [
        "Usuario"        => "tu_usuario_erp",
        "Documento"      => "Factura Electrónica",
        "Sucursal"       => "",
        "Rut"            => $order->get_billing_rut(),
        "TipoDocumento"  => "PEDIDO",
        "Folio"          => $order_id,
        "RznSocial"      => $order->get_billing_company(),
        "Giro"           => $order->get_billing_giro(),
        "Direccion"      => $order->get_billing_address_1(),
        "Ciudad"         => $order->get_billing_city(),
        "Comuna"         => $order->get_billing_state(),
        "Telefono"       => $order->get_billing_phone(),
        "Email"          => $order->get_billing_email(),
        "Fecha"          => $order->get_date_created()->date('Y-m-d\TH:i:s'),
        "Comentario"     => $order->get_customer_note(),
        "FormaPago"      => ($order->get_payment_method() == 'credit') ? '2' : '1',
        "Afecto"         => $order->get_total() - $order->get_total_tax(),
        "Exento"         => 0,
        "Descuento"      => 0,
        "TipoImpto1"     => "IVA",
        "ValorImpto1"    => $order->get_total_tax(),
        "total"          => $order->get_total(),
        "FechaVencimiento" => $order->get_date_created()->date('Y-m-d\TH:i:s'),
        "Bodega"         => "Bodega Roger",
        "EsInventariable" => "S",
        "Vendedor"       => "Renovaciones",
        "Recargo"        => 0,
        "PorcDescuento"  => 0.00,
        "PorcRecargo"    => 0.00,
        "Contacto"       => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        "Observacion"    => "",
        "Comision"       => null,
        "FichaDireccion" => "",
        "Detalle"        => []
    ];

    // Agregar los productos del pedido
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $data['Detalle'][] = [
            "Descripcion"         => $product->get_name(),
            "Cantidad"            => $item->get_quantity(),
            "PrecioUnitario"      => $item->get_total() / $item->get_quantity(),
            "Descuento"           => 0,
            "Total"               => $item->get_total(),
            "UnidadMedida"        => "UN",
            "UnidadNegocio"       => "CASA MATRIZ",
            "Articulo"            => $product->get_sku(),
            "PorcDescuento"       => 0.00,
            "DescripcionDetallada" => "",
            "Exento"              => ""
        ];
    }

    // Enviar la solicitud a la API
    $response = wp_remote_post('https://api.kameone.cl/api/Documento/addPedido', [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ],
        'body'    => json_encode($data)
    ]);

    if (is_wp_error($response)) {
        error_log('Error al enviar el pedido a KAME ERP: ' . $response->get_error_message());
    } else {
        error_log('Pedido enviado a KAME ERP exitosamente.');
    }
}

add_action('woocommerce_checkout_order_processed', 'enviar_pedido_a_kame_erp', 10, 1);
