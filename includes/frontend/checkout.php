<?php
// No cerrar la etiqueta PHP al final del archivo

// Incluir el archivo de conexión para manejar el token
require_once plugin_dir_path(__FILE__) . '../api/connection.php';

/**
 * Añadir campos personalizados al checkout
 */
function kame_erp_custom_checkout_fields() {
    // Iniciar buffer de salida para evitar salidas inesperadas
    ob_start();
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
                'boleta'  => __('Boleta', 'wp-kame-connect'),
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
                'placeholder' => 'Ejemplo: 12.345.678-9',
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

            // Formatear RUT en tiempo real
            function formatRut(rut) {
                // Eliminar caracteres no válidos
                rut = rut.replace(/[^\dkK]/g, '').toUpperCase();
                // Añadir guión antes del dígito verificador
                if (rut.length > 1) {
                    rut = rut.slice(0, -1) + '-' + rut.slice(-1);
                }
                // Añadir puntos cada tres dígitos, excepto antes del guión
                rut = rut.slice(0, -5).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + rut.slice(-5);
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
    // Finalizar y limpiar el buffer de salida
    echo ob_get_clean();
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
 * Enviar pedido a KAME ERP al procesarse el pedido
 */
function enviar_pedido_a_kame_erp($order_id) {
    // Incluir el archivo de conexión
    require_once plugin_dir_path(__FILE__) . '../api/connection.php';

    // Obtener los datos del pedido
    $order = wc_get_order($order_id);

    if (!$order) {
        error_log("[$order_id] Pedido no encontrado.\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
        return;
    }

    // Obtener tipo de documento
    $tipo_documento = $order->get_meta('tipo_documento');

    // Datos de facturación adicionales
    $razon_social = $order->get_meta('_billing_razon_social');
    $rut = $order->get_meta('_billing_rut');
    $giro = $order->get_meta('_billing_giro');

    // Formato de fecha
    $fecha_formato = 'Y-m-d\TH:i:s';

    // Obtener el total de la orden
    $total = (int) round($order->get_total());

    // Calcular Afecto y ValorImpto1
    $afecto = (int) floor($total / 1.19); // 19% IVA
    $valorImpto1 = $total - $afecto;
    $total_document = $afecto + $valorImpto1; // Debe ser igual a $total

    // Calcular el ratio para distribuir afecto en los detalles
    $ratio = $afecto / $total;

    // Preparar datos para la API
    $data = [
        "Usuario"          => "proyectos@agarthamarketing.com", // Reemplaza con tu usuario ERP
        "Documento"        => ($tipo_documento == 'factura') ? "Factura Electrónica" : "Boleta",
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
        "Fecha"            => $order->get_date_created()->date($fecha_formato),
        "Comentario"       => $order->get_customer_note() ? $order->get_customer_note() : "Venta Online", // Comentario del cliente o por defecto
        "FormaPago"        => "1", // "1" (contado), "2" (credito) - siempre "1" en este caso
        "Afecto"           => $afecto,
        "Exento"           => 0,
        "Descuento"        => 0,
        "TipoImpto1"       => "IVA",
        "ValorImpto1"      => $valorImpto1,
        "total"            => $total_document,
        "FechaVencimiento" => $order->get_date_created()->date($fecha_formato),
        "Bodega"           => "Kame", // Reemplaza con el nombre real de tu bodega en KAME ERP
        "EsInventariable"  => "S",  // "S" (si), "" (no)
        "Vendedor"         => "Kame", // Reemplaza con el nombre real de tu vendedor en KAME ERP
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

    // Agregar los productos al detalle
    $sum_detalle_total = 0;
    $items = $order->get_items();
    $total_items = count($items);
    $current_item = 0;

    foreach ($items as $item_id => $item) {
        $current_item++;
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

        // Obtener el total del item en WooCommerce (sin impuestos)
        $item_total = (int) round($item->get_total());

        // Calcular el detalle total para el item
        if ($current_item < $total_items) {
            $detalle_total_item = (int) floor($item_total * $ratio);
            $sum_detalle_total += $detalle_total_item;
        } else {
            // Para el último item, ajustar para que la suma total coincida con $afecto
            $detalle_total_item = $afecto - $sum_detalle_total;
        }

        // Calcular el precio unitario
        $quantity = (int) $item->get_quantity();
        if ($quantity > 0) {
            $precio_unitario = (int) floor($detalle_total_item / $quantity);
            // Ajustar el total del item en caso de discrepancia
            $total_item = $precio_unitario * $quantity;
            // Si hay diferencia, ajustarla en el último item
            if ($current_item == $total_items) {
                $diferencia = $detalle_total_item - $total_item;
                $total_item += $diferencia;
            }
        } else {
            $precio_unitario = 0;
            $total_item = 0;
        }

        $data['Detalle'][] = [
            "Descripcion"          => $product->get_name(),
            "Cantidad"             => $quantity,
            "PrecioUnitario"       => $precio_unitario,
            "Descuento"            => 0,
            "Total"                => $total_item,
            "UnidadMedida"         => "UN",
            "UnidadNegocio"        => "CASA MATRIZ",
            "Articulo"             => $sku,
            "PorcDescuento"        => 0.00,
            "DescripcionDetallada" => "",
            "Exento"               => ""
        ];
    }

    // Verificar que la suma de 'Detalle' sea igual a 'Afecto'
    $sum_detalle_total = array_sum(array_column($data['Detalle'], 'Total'));
    if ($sum_detalle_total !== $afecto) {
        error_log("[$order_id] La suma de 'Detalle' ($sum_detalle_total) no coincide con 'Afecto' ($afecto).\n", 3, __DIR__ . '/logs/error_log_pedidos_enviados.log');
        return;
    }

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
