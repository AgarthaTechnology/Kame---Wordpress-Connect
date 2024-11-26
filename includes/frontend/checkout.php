<?php
// No cerrar la etiqueta PHP al final del archivo

/**
 * Añadir campos personalizados al checkout
 */
function kame_erp_custom_checkout_fields() {
    // Iniciar buffer de salida para evitar salidas inesperadas
    ob_start();
    ?>

    <div id="kame_erp_custom_checkout_fields">
        <h3>Datos de Facturación</h3>
        <?php
        // Opción para elegir el tipo de documento (Boleta o Factura)
        woocommerce_form_field('tipo_documento', array(
            'type'    => 'select',
            'class'   => array('form-row-wide'),
            'label'   => 'Tipo de Documento',
            'options' => array(
                'boleta'  => 'Boleta',
                'factura' => 'Factura Electrónica',
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
                'label'    => 'Razón Social',
                'required' => false,
            ), WC()->session->get('billing_razon_social', ''));

            // RUT
            woocommerce_form_field('billing_rut', array(
                'type'        => 'text',
                'class'       => array('form-row-wide'),
                'label'       => 'RUT',
                'required'    => false,
                'placeholder' => 'Ejemplo: 12345678-9',
                'description' => 'Ingresa tu RUT sin puntos, con guión y dígito verificador.',
            ), WC()->session->get('billing_rut', ''));

            // Giro
            woocommerce_form_field('billing_giro', array(
                'type'     => 'text',
                'class'    => array('form-row-wide'),
                'label'    => 'Giro',
                'required' => false,
            ), WC()->session->get('billing_giro', ''));
            ?>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(function($){
            function toggleCamposFactura() {
                if ($('#tipo_documento').val() === 'factura') {
                    $('#campos_factura').slideDown();
                    $('#billing_razon_social, #billing_rut, #billing_giro').attr('required', true).addClass('validate-required');
                } else {
                    $('#campos_factura').slideUp();
                    $('#billing_razon_social, #billing_rut, #billing_giro').attr('required', false).removeClass('validate-required');
                }
            }

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
 * Validar campos personalizados
 */
function kame_erp_validate_custom_checkout_fields() {
    if (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'factura') {
        if (empty($_POST['billing_razon_social'])) {
            wc_add_notice('Por favor ingresa tu Razón Social.', 'error');
        }
        if (empty($_POST['billing_rut'])) {
            wc_add_notice('Por favor ingresa tu RUT.', 'error');
        } else {
            $rut = sanitize_text_field($_POST['billing_rut']);
            if (!validar_rut_chileno($rut)) {
                wc_add_notice('El RUT ingresado no es válido.', 'error');
            }
        }
        if (empty($_POST['billing_giro'])) {
            wc_add_notice('Por favor ingresa tu Giro.', 'error');
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
    echo '<p><strong>Tipo de Documento:</strong> ' . ucfirst($tipo_documento) . '</p>';

    if ($tipo_documento == 'factura') {
        $razon_social = $order->get_meta('_billing_razon_social');
        $rut = $order->get_meta('_billing_rut');
        $giro = $order->get_meta('_billing_giro');

        echo '<p><strong>Razón Social:</strong> ' . $razon_social . '</p>';
        echo '<p><strong>RUT:</strong> ' . $rut . '</p>';
        echo '<p><strong>Giro:</strong> ' . $giro . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'kame_erp_display_custom_order_data_in_admin', 10, 1);

/**
 * Función para validar el RUT Chileno
 */
function validar_rut_chileno($rut) {
    if ((empty($rut)) || strlen($rut) < 3) {
        return false;
    }

    $rutSinFormato = preg_replace('/[.\-\s]/', '', $rut);

    if (!preg_match("/^[0-9]+[0-9kK]{1}$/", $rutSinFormato)) {
        return false;
    }

    $dv = strtolower(substr($rutSinFormato, -1)); // Extraer dígito verificador
    $numero = substr($rutSinFormato, 0, strlen($rutSinFormato) - 1);

    if (strlen($numero) < 7) {
        return false;
    }

    return dv($numero) == $dv;
}

function dv($numero) {
    $M = 0;
    $S = 1;
    for (; $numero; $numero = floor($numero / 10)) {
        $S = ($S + $numero % 10 * (9 - $M++ % 6)) % 11;
    }
    return $S ? $S - 1 : 'k';
}

/**
 * Enviar pedido a KAME ERP al procesarse el pedido
 */
function enviar_pedido_a_kame_erp($order_id) {
    require_once __DIR__ . '/../api/connection.php';
    // Tu código para enviar el pedido a KAME ERP
}
add_action('woocommerce_checkout_order_processed', 'enviar_pedido_a_kame_erp', 10, 1);
