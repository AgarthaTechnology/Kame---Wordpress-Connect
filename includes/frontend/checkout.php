<?php
// No cerrar la etiqueta PHP al final del archivo

// Incluir el archivo de conexión para manejar el token
require_once plugin_dir_path(__FILE__) . '../api/connection.php';

/**
 * Añadir campos personalizados al checkout
 */
function kame_erp_custom_checkout_fields() {
    ?>
    <div id="kame_erp_custom_checkout_fields">
        <h3><?php _e('Datos de Facturación', 'wp-kame-connect'); ?></h3>
        <?php
        // Opción para elegir el tipo de documento (Boleta o Factura)
        woocommerce_form_field('tipo_documento', array(
            'type'    => 'select',
            'class'   => array('form-row-wide'),
            'label'   => __('Tipo de Documento', 'wp-kame-connect'),
            'options' => array(
                'boleta'  => __('Boleta Electrónica', 'wp-kame-connect'),
                'factura' => __('Factura Electrónica', 'wp-kame-connect'),
            ),
            'default' => 'boleta',
        ), WC()->session->get('tipo_documento', 'boleta'));

        // Campos adicionales que se mostrarán solo si se elige Factura
        ?>
        <div id="campos_factura" style="display:none;">
            <?php
            // Razón Social
            woocommerce_form_field('billing_razon_social', array(
                'type'     => 'text',
                'class'    => array('form-row-wide'),
                'label'    => __('Razón Social', 'wp-kame-connect'),
                'required' => false,
            ), WC()->session->get('billing_razon_social', ''));

            // RUT
            woocommerce_form_field('billing_rut', array(
                'type'        => 'text',
                'class'       => array('form-row-wide'),
                'label'       => __('RUT', 'wp-kame-connect'),
                'required'    => false,
                'placeholder' => 'Ejemplo: 77.214.266-8',
            ), WC()->session->get('billing_rut', ''));

            // Giro
            woocommerce_form_field('billing_giro', array(
                'type'     => 'text',
                'class'    => array('form-row-wide'),
                'label'    => __('Giro', 'wp-kame-connect'),
                'required' => false,
            ), WC()->session->get('billing_giro', ''));
            ?>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(function($){
            // Función para mostrar u ocultar campos adicionales según el tipo de documento
            function toggleCamposFactura() {
                if ($('#tipo_documento').val() === 'factura') {
                    $('#campos_factura').slideDown();
                    $('#billing_razon_social, #billing_rut, #billing_giro').attr('required', true).addClass('validate-required');
                } else {
                    $('#campos_factura').slideUp();
                    $('#billing_razon_social, #billing_rut, #billing_giro').attr('required', false).removeClass('validate-required');
                }
            }

            // Función mejorada para formatear el RUT en tiempo real
            function formatRut(rut) {
                // Eliminar caracteres no válidos
                rut = rut.replace(/[^\dkK]/g, '').toUpperCase();

                // Verificar que el RUT tenga al menos 2 caracteres (cuerpo + DV)
                if (rut.length <= 1) {
                    return rut;
                }

                // Separar el cuerpo y el dígito verificador
                var body = rut.slice(0, -1);
                var dv = rut.slice(-1);

                // Agregar puntos cada tres dígitos desde la derecha en el cuerpo
                body = body.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                // Combinar el cuerpo formateado con el DV usando un guion
                rut = body + "-" + dv;

                return rut;
            }

            $('#billing_rut').on('input', function() {
                var formattedRut = formatRut($(this).val());
                $(this).val(formattedRut);
            });

            // Ejecutar al cargar la página y cuando cambie el valor
            $(document.body).on('change', '#tipo_documento', toggleCamposFactura);
            toggleCamposFactura();
        });
    </script>
    <?php
}
add_action('woocommerce_review_order_before_payment', 'kame_erp_custom_checkout_fields');

/**
 * Validar campos personalizados en el checkout
 */
function kame_erp_validate_custom_checkout_fields() {
    if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'factura') {
        // Validar Razón Social
        if (empty($_POST['billing_razon_social'])) {
            wc_add_notice(__('Por favor ingresa tu Razón Social.', 'wp-kame-connect'), 'error');
        }

        // Validar RUT
        if (empty($_POST['billing_rut'])) {
            wc_add_notice(__('Por favor ingresa tu RUT.', 'wp-kame-connect'), 'error');
        } else {
            $rut = sanitize_text_field($_POST['billing_rut']);
            if (!validar_rut_chileno($rut)) {
                wc_add_notice(__('El RUT ingresado no es válido.', 'wp-kame-connect'), 'error');
            }
        }

        // Validar Giro
        if (empty($_POST['billing_giro'])) {
            wc_add_notice(__('Por favor ingresa tu Giro.', 'wp-kame-connect'), 'error');
        }
    }
}
add_action('woocommerce_checkout_process', 'kame_erp_validate_custom_checkout_fields');

/**
 * Guardar campos personalizados en el pedido
 */
function kame_erp_save_custom_checkout_fields_to_order($order, $data) {
    if (isset($_POST['tipo_documento'])) {
        $order->update_meta_data('tipo_documento', sanitize_text_field($_POST['tipo_documento']));
    }

    if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'factura') {
        if (isset($_POST['billing_razon_social'])) {
            $order->update_meta_data('_billing_razon_social', sanitize_text_field($_POST['billing_razon_social']));
        }

        if (isset($_POST['billing_rut'])) {
            // Eliminar puntos y guiones antes de guardar
            $rut = sanitize_text_field($_POST['billing_rut']);
            $rut_formateado = strtoupper(preg_replace('/[.\-\s]/', '', $rut));
            $order->update_meta_data('_billing_rut', $rut_formateado);
        }

        if (isset($_POST['billing_giro'])) {
            $order->update_meta_data('_billing_giro', sanitize_text_field($_POST['billing_giro']));
        }
    }
}
add_action('woocommerce_checkout_create_order', 'kame_erp_save_custom_checkout_fields_to_order', 10, 2);

/**
 * Mostrar campos personalizados en el panel de administración
 */
function kame_erp_display_custom_order_data_in_admin($order) {
    $tipo_documento = $order->get_meta('tipo_documento');
    echo '<p><strong>' . __('Tipo de Documento:', 'wp-kame-connect') . '</strong> ' . esc_html(ucfirst($tipo_documento)) . '</p>';

    if ($tipo_documento == 'factura') {
        $razon_social = $order->get_meta('_billing_razon_social');
        $rut = $order->get_meta('_billing_rut');
        $giro = $order->get_meta('_billing_giro');

        echo '<p><strong>' . __('Razón Social:', 'wp-kame-connect') . '</strong> ' . esc_html($razon_social) . '</p>';
        echo '<p><strong>' . __('RUT:', 'wp-kame-connect') . '</strong> ' . esc_html($rut) . '</p>';
        echo '<p><strong>' . __('Giro:', 'wp-kame-connect') . '</strong> ' . esc_html($giro) . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'kame_erp_display_custom_order_data_in_admin', 10, 1);

/**
 * Función para validar el RUT Chileno
 *
 * @param string $rut El RUT a validar
 * @return bool
 */
function validar_rut_chileno($rut) {
    // Eliminar todos los caracteres excepto dígitos y 'k' o 'K'
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    $rut = strtolower($rut);

    if (strlen($rut) < 2) {
        return false;
    }

    // Obtener el dígito verificador
    $dv = substr($rut, -1);
    // Obtener el número
    $numero = substr($rut, 0, -1);

    // Validar que el número sea realmente un número
    if (!is_numeric($numero)) {
        return false;
    }

    // Calcular el dígito verificador esperado
    $i = 2;
    $suma = 0;
    foreach (array_reverse(str_split($numero)) as $n) {
        if ($i > 7) { // Reiniciar a 2 después de 7
            $i = 2;
        }
        $suma += $n * $i;
        $i++;
    }
    $dvr = 11 - ($suma % 11);

    if ($dvr == 11) {
        $dvr = '0';
    } elseif ($dvr == 10) {
        $dvr = 'k';
    } else {
        $dvr = (string) $dvr;
    }

    // Comparar el dígito verificador calculado con el proporcionado
    return $dvr === $dv;
}

/**
 * Calcular el dígito verificador
 *
 * @param int $numero Número del RUT sin dígito verificador
 * @return string Dígito verificador calculado
 */
function dv($numero) {
    $M = 0;
    $S = 1;
    for (; $numero; $numero = floor($numero / 10)) {
        $S = ($S + ($numero % 10) * (9 - ($M++ % 6))) % 11;
    }
    return $S ? $S - 1 : 'k';
}


/**
 * Calcular el descuento y el exento del documento según el método de envío.
 *
 * @param WC_Order $order El pedido actual.
 * @return array Un array asociativo con los valores de 'descuento_documento' y 'exento'.
 */
function kame_erp_calcular_descuento_y_exento($order) {
    $descuento_documento = 0;
    $exento = 0;

    $shipping_methods = $order->get_shipping_methods();

    foreach ($shipping_methods as $shipping_item_id => $shipping_item) {
        $shipping_method_id = $shipping_item->get_method_id();

        switch ($shipping_method_id) {
            case 'flat_rate': // Método de envío estándar
                $descuento_documento += $order->get_total_discount();
                $exento += $shipping_item->get_total(); // Envío sin impuestos
                break;

            case 'free_shipping': // Envío gratuito
            case 'local_pickup':  // Recogida local
                $descuento_documento += $order->get_total_discount() + 1; // Total descuento + 1
                if ($exento === 0) {
                    $exento += 1; // Ajustar exento simbólico a 1 solo si exento es 0
                }
                break;

            default: // Otros métodos de envío
                $descuento_documento += $order->get_total_discount();
                $exento += $shipping_item->get_total(); // Envío sin impuestos
                break;
        }
    }

    return [
        'descuento_documento' => $descuento_documento,
        'exento' => $exento,
    ];
}




/**
 * Enviar pedido a KAME ERP al procesarse el pedido
 */
function enviar_pedido_a_kame_erp($order_id) {
    // Obtener los datos del pedido
    $order = wc_get_order($order_id);

    if (!$order) {
        error_log("[$order_id] Pedido no encontrado.\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
        return;
    }

    // Obtener tipo de documento
    $tipo_documento = $order->get_meta('tipo_documento');
    
     // Calcular el descuento del documento y el exento
    $calculos = kame_erp_calcular_descuento_y_exento($order);
    $descuento_documento = $calculos['descuento_documento'];
    $exento = $calculos['exento'];

    // Datos de facturación adicionales
    $razon_social = $order->get_meta('_billing_razon_social');
    $rut = $order->get_meta('_billing_rut');
    $giro = $order->get_meta('_billing_giro');

    // Formato de fecha
    $fecha_formato = 'Y-m-d\TH:i:s';
    $fecha_vencimiento = $order->get_date_created()->date($fecha_formato);

    // Totales del pedido
    $afecto = $order->get_subtotal(); // Total sin impuestos
    $valorImpto1 = $order->get_total_tax(); // Total de impuestos
    $total_document = $order->get_total(); // Suma de Afecto + Exento + ValorImpto1
    
    // Preparar datos para la API
    $data = [
        "Usuario"          => "proyectos@agarthamarketing.com", // Reemplaza con tu usuario ERP
        "Documento"        => ($tipo_documento == 'factura') ? "Factura Electrónica" : "Boleta Electrónica",
        "Sucursal"         => "", // Si es vacío corresponde a MATRIZ
        "Rut"              => ($tipo_documento == 'factura') ? preg_replace('/[.\-]/', '', $rut) : "11111111-1", // RUT sin puntos ni guion para factura, genérico para boleta
        "TipoDocumento"    => "PEDIDO", // Este valor es fijo
        "Folio"            => (int)$order_id,
        "RznSocial"        => ($tipo_documento == 'factura') ? $razon_social : $order->get_formatted_billing_full_name(),
        "Giro"             => ($tipo_documento == 'factura') ? $giro : "Particular",
        "Direccion"        => $order->get_billing_address_1(),
        "Ciudad"           => $order->get_billing_city(),
        "Comuna"           => $order->get_billing_state(),
        "Telefono"         => $order->get_billing_phone(),
        "Email"            => $order->get_billing_email(),
        "Fecha"            => $fecha_vencimiento,
        "Comentario"       => $order->get_customer_note() ? $order->get_customer_note() : "Venta WEB", // Comentario del cliente o por defecto
        "FormaPago"        => "1", // "1" (contado), "2" (credito) - siempre "1" en este caso
        "Afecto"           => $afecto, // Total sin impuestos
        "Exento"           => $exento, // Total de envío sin impuestos
        "Descuento"        => $descuento_documento,
        "TipoImpto1"       => "IVA",
        "ValorImpto1"      => $valorImpto1, // Total de impuestos
        "total"            => $total_document, // Suma de Afecto + Exento + ValorImpto1
        "FechaVencimiento" => $fecha_vencimiento,
        "Bodega"           => "KAME", // Reemplaza con el nombre real de tu bodega en KAME ERP
        "EsInventariable"  => "S",  // "S" (si), "" (no)
        "Vendedor"         => "KAME", // Reemplaza con el nombre real de tu vendedor en KAME ERP
        "Recargo"          => 0,
        "PorcDescuento"    => 0.00,
        "PorcRecargo"      => 0.00,
        "Contacto"         => $order->get_formatted_billing_full_name(),
        "Observacion"      => "",
        "Comision"         => null, // Porcentaje
        "FichaDireccion"   => "",
        "Detalle"          => [],
        // "Cuotas"         => [], // Eliminado porque siempre es contado
        // "Referencias"    => []  // Puedes agregar referencias si es necesario
    ];

    /**
     * Generar el detalle de los productos y el envío
     */

    // Agregar los productos al detalle
    $items = $order->get_items();
    $sum_detalle_total = 0; // Inicializar la suma de 'Detalle'

    foreach ($items as $item_id => $item) {
        $product = $item->get_product();
        if (!$product) {
            error_log("[$order_id] Producto no encontrado para el item ID: " . $item_id . "\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
            continue;
        }
        
        $sku = $product->get_sku();
        if (!$sku) {
            error_log("[$order_id] El producto ID " . $product->get_id() . " no tiene SKU. Omitido.\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
            continue;
        }

        // Obtener la cantidad y el total del ítem directamente de WooCommerce
        $quantity = $item->get_quantity();
            $item_total = ($tipo_documento === 'factura') ? $item->get_total() : ($item->get_total() + $item->get_total_tax());


        // Obtener el precio unitario directamente del producto
        $precio_unitario = ($tipo_documento === 'factura') ? $product->get_price() : wc_get_price_including_tax($product);

        $data['Detalle'][] = [
            "Descripcion"          => $product->get_name(),
            "Cantidad"             => $quantity,
            "PrecioUnitario"       => $precio_unitario,
            "Descuento"            => 0,
            "Total"                => $item_total,
            "UnidadMedida"         => "UN",
            "UnidadNegocio"        => "CASA MATRIZ",
            "Articulo"             => $sku,
            "PorcDescuento"        => 0.00,
            "DescripcionDetallada" => "",
            "Exento"               => ""
        ];

        $sum_detalle_total += $item_total;
    }

    // Agregar el Envío como un Ítem en el Detalle
    $shipping_methods = $order->get_shipping_methods();

    foreach ($shipping_methods as $shipping_item_id => $shipping_item) {
        $shipping_method_id = $shipping_item->get_method_id(); // Obtener el método de envío ID
        $shipping_method_name = $shipping_item->get_name(); // Nombre del método de envío
        $shipping_total = $shipping_item->get_total(); // Total de envío sin impuestos

        // Inicializar valores predeterminados para envío
    $shipping_total = 0;
    $shipping_dcto = 0;

    // Determinar la descripción, total y porcentaje de descuento según el método de envío
    switch ($shipping_method_id) {
        case 'flat_rate': // Método de envío estándar
            $descripcion_envio = "ENVIO";
            $shipping_total = $shipping_item->get_total(); // Total sin modificaciones
            $shipping_dcto = 0; // Sin descuento
            break;

        case 'free_shipping': // Envío gratuito
            $descripcion_envio = "ENVIO GRATUITO";
            $shipping_total = 1; // Precio simbólico de 1 peso
            $shipping_dcto = 1; // Descuento simbólico de 1 peso
            break;

        case 'local_pickup': // Retiro en tienda
            $descripcion_envio = "RETIRO EN TIENDA";
            $shipping_total = 1; // Precio simbólico de 1 peso
            $shipping_dcto = 1; // Descuento simbólico de 1 peso
            break;

        default: // Cualquier otro método de envío
            $descripcion_envio = "Envío: " . $shipping_method_name;
            $shipping_total = $shipping_item->get_total(); // Usar el total predeterminado
            $shipping_dcto = 0; // Sin descuento
            break;
    }

        // Verificar que el total de envío sea mayor a cero o que el método de envío sea 'free_shipping' o 'local_pickup' para agregar
        if ($shipping_total > 0 || in_array($shipping_method_id, ['free_shipping', 'local_pickup'])) {
            $data['Detalle'][] = [
                "Descripcion"          => $descripcion_envio, // Descripción modificada
                "Cantidad"             => 1.000000,
                "PrecioUnitario"       => $shipping_total,
                "Descuento"            => $shipping_dcto,
                "Total"                => $shipping_total,
                "UnidadMedida"         => "UN",
                "UnidadNegocio"        => "CASA MATRIZ",
                "Articulo"             => $descripcion_envio, // Asignar SKU según el método
                "PorcDescuento"        => 0, // Porcentaje de descuento
                "DescripcionDetallada" => "",
                "Exento"               => "S",
            ];

            // Añadir al sumatorio total
            $sum_detalle_total += $shipping_total;

            // Opcional: Añadir log para verificar
            error_log("[$order_id] Añadido envío al Detalle: $descripcion_envio - $shipping_total\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
        }
    }

    /**
     * Continuar con el envío del documento
     */

    // Asegurar que el archivo de registro exista y tenga permisos adecuados
    $log_dir = __DIR__ . '/logs/';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . 'error_log_pedidos_enviados.log';
    if (!file_exists($log_file)) {
        file_put_contents($log_file, '');
        chmod($log_file, 0664);
    }

    // Agregar logs detallados antes de proceder
    error_log("[$order_id] Afecto (Subtotal): $afecto\n", 3, $log_file);
    error_log("[$order_id] Exento (Envío): $exento\n", 3, $log_file);
    error_log("[$order_id] Suma de 'Detalle': $sum_detalle_total\n", 3, $log_file);

    // Log de verificación exitosa
    error_log("[$order_id] Continuando con el envío del pedido.\n", 3, $log_file);

    // Obtener el token de acceso y verificar si ha expirado
    $access_token = get_option('kame_erp_access_token');
    $token_expiration = get_option('kame_erp_token_expiration', 0);

    // Verificar si el token ha expirado
    if (time() >= $token_expiration) {
        error_log("[$order_id] El token ha expirado, obteniendo uno nuevo...\n", 3, $log_file);
        $token_result = fetch_and_store_kame_erp_access_token();

        if (!$token_result['success']) {
            error_log("[$order_id] Error al obtener el token de acceso: " . $token_result['message'] . "\n", 3, $log_file);
            return;
        } else {
            error_log("[$order_id] Nuevo token de acceso obtenido.\n", 3, $log_file);
        }

        // Obtener el nuevo token
        $access_token = get_option('kame_erp_access_token');
    } else {
        error_log("[$order_id] Token de acceso válido encontrado.\n", 3, $log_file);
    }

    // Enviar la solicitud a la API
    $response = wp_remote_post('https://api.kameone.cl/api/Documento/addPedido', [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ],
        'body'    => json_encode($data),
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        error_log("[$order_id] Error al enviar el pedido a KAME ERP: " . $response->get_error_message() . "\n", 3, $log_file);
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Log del cuerpo de la respuesta para mayor detalle
        error_log("[$order_id] Respuesta de la API: $body\n", 3, $log_file);

        // Decodificar la respuesta JSON
        $response_data = json_decode($body, true);

        // Verificar si la API reporta estado de error
        if (isset($response_data['Estado']) && strtolower($response_data['Estado']) === 'error') {
            // Registrar cada error individualmente
            if (isset($response_data['Error']) && is_array($response_data['Error'])) {
                foreach ($response_data['Error'] as $error) {
                    $fields = implode(', ', $error['MemberNames']);
                    $message = $error['ErrorMessage'];
                    error_log("[$order_id] Error en $fields: $message\n", 3, $log_file);
                }
            }
            // Registrar que el pedido no fue exitoso
            error_log("[$order_id] Pedido no enviado a KAME ERP debido a errores.\n", 3, $log_file);
        } else {
            // Si el estado no es error, asumir éxito
            if ($status_code == 200) {
                error_log("[$order_id] Pedido enviado a KAME ERP exitosamente.\n", 3, $log_file);
            } else {
                error_log("[$order_id] Error al enviar el pedido a KAME ERP. Código de estado: " . $status_code . ". Respuesta: " . $body . "\n", 3, $log_file);
            }
        }
    }
}
add_action('woocommerce_checkout_order_processed', 'enviar_pedido_a_kame_erp', 10, 1);
