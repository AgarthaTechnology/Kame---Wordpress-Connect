
<?php
/**
 * Plugin Name: Kame ERP - WooCommerce Integration
 * Description: Configura las credenciales de la API de KAME ERP desde el panel de administrador de WordPress. Incluye integración con el checkout de WooCommerce, envío de datos de venta al ERP, sincronización de inventario, y gestión de bodegas. Diseñado por Agartha Marketing Agency.
 * Version: 2.7.9
 * Author: Agartha Marketing Agency
 * Author URI: https://agarthamarketing.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// =====================
// 2. REGISTRO DE ACTIVACIÓN
// =====================
register_activation_hook(__FILE__, function() {
    ob_start(); // Prevenir salida inesperada
    kame_erp_initialize_data_table();
    ob_end_clean();
});

// =====================
// RESTO DEL PLUGIN
// =====================
/*
Plugin Name: Kame ERP - WooCommerce Integration
Description: Configura las credenciales de la API de KAME ERP desde el panel de administrador de WordPress. Incluye integración con el checkout de WooCommerce, envío de datos de venta al ERP, sincronización de inventario, y gestión de bodegas. Diseñado por Agartha Marketing Agency.
Version: 2.7.9
Author: Agartha Marketing Agency
Author URI: https://agarthamarketing.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// =====================
// 1. FUNCIONES PRINCIPALES
// =====================

// Función para obtener el token de acceso
function kame_erp_get_access_token() {
    $client_id = get_option('kame_erp_client_id');
    $client_secret = get_option('kame_erp_client_secret');

    if (!$client_id || !$client_secret) {
        error_log('KAME ERP: Las credenciales no están configuradas.');
        return false;
    }

    $response = wp_remote_post('https://api.kameone.cl/oauth/token', array(
        'method'    => 'POST',
        'headers'   => array(
            'Content-Type'  => 'application/json',
        ),
        'body'      => json_encode(array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'audience'      => 'https://api.kameone.cl/api',
            'grant_type'    => 'client_credentials',
        )),
        'timeout'   => 30,
    ));

    if (is_wp_error($response)) {
        error_log('KAME ERP: Error al obtener el token de acceso.');
        error_log($response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($status_code == 200 && isset($data['access_token'])) {
        return $data['access_token'];
    } else {
        error_log('KAME ERP: Respuesta inesperada al obtener el token de acceso.');
        error_log('Código de estado: ' . $status_code);
        error_log('Respuesta: ' . $body);
        return false;
    }
}

// Función para verificar la conexión
function kame_erp_check_connection() {
    $access_token = kame_erp_get_access_token();
    return $access_token ? true : false;
}

// =====================
// 2. MENÚ ADMINISTRATIVO
// =====================
add_action('admin_menu', 'kame_erp_menu');
function kame_erp_menu() {
    add_menu_page(
        'Configuración KAME ERP',
        'KAME ERP',
        'manage_options',
        'kame-erp-config',
        'kame_erp_config_page',
        'dashicons-admin-generic',
        50
    );

    add_submenu_page(
        'kame-erp-config',
        'Funciones del Plugin',
        'Funciones',
        'manage_options',
        'kame-erp-functions',
        'kame_erp_functions_page'
    );

    add_submenu_page(
        'kame-erp-config',
        'Sincronización de Inventario',
        'Sincronización de Inventario',
        'manage_options',
        'kame-erp-inventory',
        'kame_erp_inventory_page'
    );

    add_submenu_page(
        'kame-erp-config',
        'Importar Productos desde KAME ERP',
        'Importar Productos',
        'manage_options',
        'kame-erp-import-products',
        'kame_erp_import_products_page'
    );

    add_submenu_page(
        'kame-erp-config',
        'Acerca de este plugin',
        'Créditos',
        'manage_options',
        'kame-erp-credits',
        'kame_erp_credits_page'
    );
}

// =====================
// 3. PÁGINAS DEL PLUGIN
// =====================

function kame_erp_config_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }
    ?>
    <div class="wrap">
        <h1>Configuración KAME ERP</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_settings');
            do_settings_sections('kame-erp-config');
            submit_button();
            ?>
        </form>

        <?php
        $connection_status = kame_erp_check_connection();
        $status_text = $connection_status ? 'Online' : 'Offline';
        $status_class = $connection_status ? 'kame-erp-status-online' : 'kame-erp-status-offline';
        ?>
        <h2 style="text-align: center;">Estado de Conexión con KAME ERP: <span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span></h2>
<?php
// Mostrar el estado del Access Token
$access_token = get_option('kame_erp_access_token');
$token_status_text = $access_token ? 'Token Disponible' : 'Token No Disponible';
$token_status_class = $access_token ? 'kame-erp-status-online' : 'kame-erp-status-offline';
?>
<h2 style="text-align: center;">Estado del Access Token: <span class="<?php echo esc_attr($token_status_class); ?>"><?php echo esc_html($token_status_text); ?></span></h2>


        <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
            <p>Diseñado por <a href="https://agarthamarketing.com" target="_blank">Agartha Marketing Agency</a></p>
        </div>
    </div>
    <?php
}

function kame_erp_functions_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }
    ?>
    <div class="wrap">
        <h1>Funciones del Plugin</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_functions_settings');
            do_settings_sections('kame-erp-functions');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function kame_erp_inventory_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }

    if (isset($_POST['kame_erp_manual_sync'])) {
        check_admin_referer('kame_erp_manual_sync_action', 'kame_erp_manual_sync_nonce');
        kame_erp_synchronize_inventory();
        echo '<div class="notice notice-success"><p>Sincronización de inventario completada.</p></div>';
    }

    $last_sync = get_option('kame_erp_last_sync_time', false);
    ?>
    <div class="wrap">
        <h1>Sincronización de Inventario</h1>
        <?php if ($last_sync): ?>
            <p>Última sincronización realizada el: <?php echo date_i18n('d/m/Y H:i:s', $last_sync); ?></p>
        <?php else: ?>
            <p>Aún no se ha realizado ninguna sincronización.</p>
        <?php endif; ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('kame_erp_inventory_settings');
            do_settings_sections('kame-erp-inventory');
            submit_button();
            ?>
        </form>
        <form method="post" action="">
            <?php wp_nonce_field('kame_erp_manual_sync_action', 'kame_erp_manual_sync_nonce'); ?>
            <input type="submit" name="kame_erp_manual_sync" class="button button-primary" value="Sincronizar Ahora">
        </form>
    </div>
    <?php
}

function kame_erp_import_products_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }

    if (isset($_POST['kame_erp_import_products'])) {
        check_admin_referer('kame_erp_import_products_action', 'kame_erp_import_products_nonce');
        kame_erp_import_products();
        echo '<div class="notice notice-success"><p>Importación de productos completada.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Importar Productos desde KAME ERP</h1>
        <p>Esta función permite importar productos desde KAME ERP a WooCommerce. Úsala solo para una carga inicial de productos.</p>
        <form method="post" action="">
            <?php wp_nonce_field('kame_erp_import_products_action', 'kame_erp_import_products_nonce'); ?>
            <input type="submit" name="kame_erp_import_products" class="button button-primary" value="Importar Productos">
        </form>
    </div>
    <?php
}

function kame_erp_credits_page() {
    ?>
    <div class="wrap">
        <h1>Acerca de este plugin</h1>
        <p>Este plugin fue diseñado y desarrollado por <a href="https://agarthamarketing.com" target="_blank">Agartha Marketing Agency</a>.</p>
        <p>Para más información, visita nuestro sitio web: <a href="https://agarthamarketing.com" target="_blank">https://agarthamarketing.com</a>.</p>
    </div>
    <?php
}

// =====================
// 4. REGISTRO DE CONFIGURACIONES
// =====================

add_action('admin_init', 'kame_erp_settings_init');
function kame_erp_settings_init() {
    register_setting('kame_erp_settings', 'kame_erp_client_id', 'sanitize_text_field');
    register_setting('kame_erp_settings', 'kame_erp_client_secret', 'sanitize_text_field');
    register_setting('kame_erp_settings', 'kame_erp_usuario_kame', 'sanitize_text_field');

    add_settings_section(
        'kame_erp_section',
        'Credenciales API de KAME ERP',
        'kame_erp_section_callback',
        'kame-erp-config'
    );

    add_settings_field(
        'kame_erp_client_id',
        'Client ID',
        'kame_erp_client_id_callback',
        'kame-erp-config',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_client_secret',
        'Client Secret',
        'kame_erp_client_secret_callback',
        'kame-erp-config',
        'kame_erp_section'
    );

    add_settings_field(
        'kame_erp_usuario_kame',
        'Usuario KAME ERP',
        'kame_erp_usuario_kame_callback',
        'kame-erp-config',
        'kame_erp_section'
    );
}

function kame_erp_section_callback() {
    echo '<p>Introduce las credenciales para conectar con la API de KAME ERP. Estos datos son sensibles, asegúrate de ingresarlos correctamente.</p>';
}

function kame_erp_client_id_callback() {
    $client_id = get_option('kame_erp_client_id', '');
    echo '<input type="text" name="kame_erp_client_id" value="' . esc_attr($client_id) . '" style="width: 100%;" required>';
}

function kame_erp_client_secret_callback() {
    $client_secret = get_option('kame_erp_client_secret', '');
    echo '<input type="password" name="kame_erp_client_secret" value="' . esc_attr($client_secret) . '" style="width: 100%;" required>';
}

function kame_erp_usuario_kame_callback() {
    $usuario_kame = get_option('kame_erp_usuario_kame', '');
    echo '<input type="text" name="kame_erp_usuario_kame" value="' . esc_attr($usuario_kame) . '" style="width: 100%;" required>';
}

// Configuraciones de las funciones del plugin
add_action('admin_init', 'kame_erp_functions_settings_init');
function kame_erp_functions_settings_init() {
    register_setting('kame_erp_functions_settings', 'kame_erp_enable_integration', 'boolval');
    register_setting('kame_erp_functions_settings', 'kame_erp_enable_sync', 'boolval');

    add_settings_section(
        'kame_erp_functions_section',
        'Activar/Desactivar Funciones',
        'kame_erp_functions_section_callback',
        'kame-erp-functions'
    );

    add_settings_field(
        'kame_erp_enable_integration',
        'Integración con KAME ERP (Envío de datos de venta)',
        'kame_erp_enable_integration_callback',
        'kame-erp-functions',
        'kame_erp_functions_section'
    );

    add_settings_field(
        'kame_erp_enable_sync',
        'Sincronización de Inventario',
        'kame_erp_enable_sync_callback',
        'kame-erp-functions',
        'kame_erp_functions_section'
    );
}

function kame_erp_functions_section_callback() {
    echo '<p>Puedes activar o desactivar las funciones integradas del plugin según tus necesidades.</p>';
}

function kame_erp_enable_integration_callback() {
    $enabled = get_option('kame_erp_enable_integration', true);
    echo '<input type="checkbox" name="kame_erp_enable_integration" value="1"' . checked(1, $enabled, false) . '>';
    echo '<label for="kame_erp_enable_integration"> Habilitar la integración con KAME ERP (Envío de datos de venta)</label>';
}

function kame_erp_enable_sync_callback() {
    $enabled = get_option('kame_erp_enable_sync', true);
    echo '<input type="checkbox" name="kame_erp_enable_sync" value="1"' . checked(1, $enabled, false) . '>';
    echo '<label for="kame_erp_enable_sync"> Habilitar la sincronización de inventario</label>';
}

// Configuraciones para la sincronización de inventario
add_action('admin_init', 'kame_erp_inventory_settings_init');
function kame_erp_inventory_settings_init() {
    register_setting('kame_erp_inventory_settings', 'kame_erp_warehouses', 'kame_erp_sanitize_warehouses');
    register_setting('kame_erp_inventory_settings', 'kame_erp_sync_frequency', 'intval');

    add_settings_section(
        'kame_erp_inventory_section',
        'Configuración de Sincronización',
        'kame_erp_inventory_section_callback',
        'kame-erp-inventory'
    );

    add_settings_field(
        'kame_erp_warehouses',
        'Bodegas',
        'kame_erp_warehouses_callback',
        'kame-erp-inventory',
        'kame_erp_inventory_section'
    );

    add_settings_field(
        'kame_erp_sync_frequency',
        'Frecuencia de Sincronización (en minutos)',
        'kame_erp_sync_frequency_callback',
        'kame-erp-inventory',
        'kame_erp_inventory_section'
    );

    // Programar la sincronización cuando se actualizan las opciones
    add_action('update_option_kame_erp_sync_frequency', 'kame_erp_schedule_inventory_sync');
    add_action('update_option_kame_erp_warehouses', 'kame_erp_schedule_inventory_sync');
}

function kame_erp_inventory_section_callback() {
    echo '<p>Configura las opciones de sincronización de inventario.</p>';
}

function kame_erp_warehouses_callback() {
    $warehouses = get_option('kame_erp_warehouses', array());
    if (!is_array($warehouses)) {
        $warehouses = array();
    }
    $warehouses_text = implode("\n", $warehouses);
    echo '<textarea name="kame_erp_warehouses" rows="5" cols="50" placeholder="Ingrese los nombres de las bodegas, una por línea.">' . esc_textarea($warehouses_text) . '</textarea>';
    echo '<p class="description">Introduce los nombres de las bodegas exactamente como aparecen en KAME ERP, una por línea.</p>';
}

function kame_erp_sync_frequency_callback() {
    $frequency = get_option('kame_erp_sync_frequency', 60); // Valor predeterminado de 60 minutos
    echo '<input type="number" name="kame_erp_sync_frequency" value="' . esc_attr($frequency) . '" min="1" />';
    echo '<p class="description">Especifica la frecuencia con la que se sincronizará el inventario (en minutos).</p>';
}

function kame_erp_sanitize_warehouses($input) {
    $lines = explode("\n", $input);
    $warehouses = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            $warehouses[] = $line;
        }
    }
    return $warehouses;
}

// =====================
// 5. CAMPOS PERSONALIZADOS EN PRODUCTOS
// =====================

// Añadir campo de Bodega en la ficha de producto
add_action('woocommerce_product_options_general_product_data', 'kame_erp_add_warehouse_field');
function kame_erp_add_warehouse_field() {
    global $post;

    $warehouses = get_option('kame_erp_warehouses', array());
    if (!is_array($warehouses)) {
        $warehouses = array();
    }

    $options = array('' => __('Selecciona una bodega'));
    foreach ($warehouses as $warehouse) {
        $options[$warehouse] = $warehouse;
    }

    woocommerce_wp_select(array(
        'id' => '_kame_erp_warehouse',
        'label' => __('Bodega asociada', 'woocommerce'),
        'options' => $options,
        'description' => __('Selecciona la bodega asociada a este producto.', 'woocommerce'),
        'desc_tip' => true,
    ));
}

// Guardar el valor del campo de Bodega
add_action('woocommerce_process_product_meta', 'kame_erp_save_warehouse_field');
function kame_erp_save_warehouse_field($post_id) {
    $warehouse = isset($_POST['_kame_erp_warehouse']) ? sanitize_text_field($_POST['_kame_erp_warehouse']) : '';
    update_post_meta($post_id, '_kame_erp_warehouse', $warehouse);
}

// =====================
// 6. CAMPOS PERSONALIZADOS EN CHECKOUT DE WOOCOMMERCE
// =====================

add_action('woocommerce_after_checkout_billing_form', 'custom_checkout_field');
function custom_checkout_field($checkout) {
    echo '<div id="custom_checkout_field"><h2>' . __('Tipo de Documento') . '</h2>';

    // Campo para seleccionar el tipo de documento (boleta o factura)
    woocommerce_form_field('Documento', array(
        'type'    => 'select',
        'class'   => array('form-row-wide'),  // Este campo ocupa toda la fila
        'label'   => __('Seleccione Boleta o Factura'),
        'options' => array(
            ''        => __('Selecciona una opción'),
            'boleta'  => __('Boleta'),
            'factura' => __('Factura'),
        ),
    ), $checkout->get_value('Documento'));

    // Campos adicionales solo si se elige "Factura"
    echo '<div id="campos_factura" style="display:none;">';

    // Campo para RUT (Primera columna)
    woocommerce_form_field('Rut', array(
        'type'     => 'text',
        'class'    => array('form-row-first'),  // Primera columna
        'label'    => __('RUT (Requerido para Factura)'),
        'required' => true,
    ), $checkout->get_value('Rut'));

    // Campo para Razón Social (Segunda columna)
    woocommerce_form_field('RznSocial', array(
        'type'     => 'text',
        'class'    => array('form-row-last'),  // Segunda columna
        'label'    => __('Razón Social (Requerido para Factura)'),
        'required' => true,
    ), $checkout->get_value('RznSocial'));

    // Campo para Giro (Debe ocupar toda la fila)
    woocommerce_form_field('Giro', array(
        'type'     => 'text',
        'class'    => array('form-row-wide'),  // Todo el ancho
        'label'    => __('Giro (Requerido para Factura)'),
        'required' => true,
    ), $checkout->get_value('Giro'));

    echo '</div>';

    // Cierre del div abierto al inicio de la función
    echo '</div>';
}

add_action('wp_footer', 'mostrar_campos_factura');
function mostrar_campos_factura() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('#Documento').change(function() {
                    if ($(this).val() === 'factura') {
                        $('#campos_factura').show();
                    } else {
                        $('#campos_factura').hide();
                    }
                }).change();
            });
        </script>
        <?php
    }
}

add_action('woocommerce_checkout_process', 'validar_campos_factura');
function validar_campos_factura() {
    if (isset($_POST['Documento']) && $_POST['Documento'] === 'factura') {
        if (empty($_POST['Rut'])) {
            wc_add_notice(__('Por favor ingresa tu RUT para la factura.'), 'error');
        }
        if (empty($_POST['RznSocial'])) {
            wc_add_notice(__('Por favor ingresa tu Razón Social para la factura.'), 'error');
        }
        if (empty($_POST['Giro'])) {
            wc_add_notice(__('Por favor ingresa tu Giro para la factura.'), 'error');
        }
    }
}

add_action('woocommerce_checkout_update_order_meta', 'guardar_campos_factura');
function guardar_campos_factura($order_id) {
    if (!empty($_POST['Documento'])) {
        $tipo_documento = ($_POST['Documento'] === 'factura') ? 'Factura Electrónica' : 'Boleta';
        update_post_meta($order_id, 'Documento', sanitize_text_field($tipo_documento));
    }

    if (!empty($_POST['Rut'])) {
        update_post_meta($order_id, 'Rut', sanitize_text_field($_POST['Rut']));
    }
    if (!empty($_POST['RznSocial'])) {
        update_post_meta($order_id, 'RznSocial', sanitize_text_field($_POST['RznSocial']));
    }
    if (!empty($_POST['Giro'])) {
        update_post_meta($order_id, 'Giro', sanitize_text_field($_POST['Giro']));
    }
}

add_action('woocommerce_admin_order_data_after_billing_address', 'mostrar_campos_factura_admin', 10, 1);
function mostrar_campos_factura_admin($order) {
    $documento = get_post_meta($order->get_id(), 'Documento', true);
    if ($documento) {
        echo '<p><strong>' . __('Documento') . ':</strong> ' . esc_html($documento) . '</p>';
    }
    $rut = get_post_meta($order->get_id(), 'Rut', true);
    if ($rut) {
        echo '<p><strong>' . __('RUT') . ':</strong> ' . esc_html($rut) . '</p>';
    }
    $razon_social = get_post_meta($order->get_id(), 'RznSocial', true);
    if ($razon_social) {
        echo '<p><strong>' . __('Razón Social') . ':</strong> ' . esc_html($razon_social) . '</p>';
    }
    $giro = get_post_meta($order->get_id(), 'Giro', true);
    if ($giro) {
        echo '<p><strong>' . __('Giro') . ':</strong> ' . esc_html($giro) . '</p>';
    }
}

// =====================
// 7. ENVIAR DATOS DE VENTA AL ERP
// =====================
add_action('woocommerce_order_status_completed', 'kame_erp_enviar_datos_venta', 10, 1);
function kame_erp_enviar_datos_venta($order_id) {
    // Verificar si la integración está habilitada
    $integration_enabled = get_option('kame_erp_enable_integration', true);
    if (!$integration_enabled) {
        return;
    }

    // Obtener la orden
    $order = wc_get_order($order_id);

    // Obtener el Usuario de KAME ERP desde las configuraciones
    $usuario_kame = get_option('kame_erp_usuario_kame');

    if (!$usuario_kame) {
        error_log('KAME ERP: El usuario de KAME ERP no está configurado.');
        return;
    }

    // Determinar el tipo de documento
    $documento = get_post_meta($order_id, 'Documento', true);
    $documento = $documento === 'Factura Electrónica' ? 'Factura Electrónica' : 'Boleta';

    // Obtener la información del cliente
    $rut = get_post_meta($order_id, 'Rut', true);
    $razon_social = get_post_meta($order_id, 'RznSocial', true);
    $giro = get_post_meta($order_id, 'Giro', true);

    // Preparar los datos de la venta
    $datos_venta = array(
        'Usuario'        => $usuario_kame,
        'Documento'      => $documento,
        'Sucursal'       => '', // Si es vacío corresponde a MATRIZ
        'Rut'            => $rut ? $rut : '', // Si es Boleta y no hay RUT, dejar vacío
        'TipoDocumento'  => 'VENTA',
        'Folio'          => '', // Dejar vacío para generar folio automático
        'RznSocial'      => $razon_social ? $razon_social : $order->get_formatted_billing_full_name(),
        'Giro'           => $giro ? $giro : '',
        'Direccion'      => $order->get_billing_address_1(),
        'Ciudad'         => $order->get_billing_city(),
        'Comuna'         => $order->get_billing_state(),
        'Telefono'       => $order->get_billing_phone(),
        'Email'          => $order->get_billing_email(),
        'Fecha'          => gmdate('Y-m-d\TH:i:s', strtotime($order->get_date_created())),
        'Comentario'     => $order->get_customer_note(),
        'FormaPago'      => '1', // Asumimos contado
        'Afecto'         => $order->get_total() - $order->get_total_tax(),
        'Exento'         => 0,
        'Descuento'      => 0, // Puedes calcular el descuento si aplica
        'TipoImpto1'     => 'IVA',
        'ValorImpto1'    => $order->get_total_tax(),
        'total'          => $order->get_total(),
        'FechaVencimiento'=> gmdate('Y-m-d\TH:i:s', strtotime($order->get_date_created())),
        'Bodega'         => '', // Ajusta según tus necesidades
        'EsInventariable'=> 'S', // Asumimos que sí
        'Vendedor'       => '', // Si tienes vendedores específicos
        'Detalle'        => array(),
        // Puedes incluir otros campos según sea necesario
    );

    // Agregar los detalles de los productos
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $sku = $product->get_sku();
        $datos_venta['Detalle'][] = array(
            'Descripcion'       => $product->get_name(),
            'Cantidad'          => $item->get_quantity(),
            'PrecioUnitario'    => $product->get_price(),
            'Descuento'         => 0, // Ajusta si hay descuentos
            'Total'             => $item->get_total(),
            'UnidadMedida'      => 'UN', // Asumiendo unidad
            'Articulo'          => $sku ? $sku : $product->get_id(),
            'PorcDescuento'     => 0.00,
            'DescripcionDetallada'=> '',
            'Exento'            => '',
            'TipoExento'        => '',
            'CuentaEmpresaVenta'=> '',
            'TipoArticulo'      => '', // '', 'SERVICIO', 'PACK', 'ARTICULO'
        );
    }

    // Enviar los datos al ERP
    kame_erp_enviar_a_api($datos_venta);
}

function kame_erp_enviar_a_api($datos_venta) {
    // Obtener el token de acceso
    $access_token = kame_erp_get_access_token();
    if (!$access_token) {
        error_log('KAME ERP: No se pudo obtener el token de acceso.');
        return;
    }

    // Endpoint de la API
    $api_url = 'https://api.kameone.cl/api/Documento/addVenta';

    // Configurar la solicitud
    $response = wp_remote_post($api_url, array(
        'method'  => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode($datos_venta),
        'timeout' => 30,
    ));

    // Manejar la respuesta
    if (is_wp_error($response)) {
        error_log('KAME ERP: Error al conectar con la API.');
        error_log($response->get_error_message());
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($status_code == 200 || $status_code == 201) {
            // Éxito
            error_log('KAME ERP: Datos de venta enviados correctamente.');
        } else {
            // Error en la API
            error_log('KAME ERP: Error en la API.');
            error_log('Código de estado: ' . $status_code);
            error_log('Respuesta: ' . $body);
        }
    }
}

// =====================
// 8. SINCRONIZACIÓN DE INVENTARIO
// =====================

function kame_erp_get_stock_by_warehouse($warehouse_name) {
    $access_token = kame_erp_get_access_token();
    if (!$access_token) {
        error_log('KAME ERP: No se pudo obtener el token de acceso.');
        return false;
    }

    $warehouse_encoded = urlencode($warehouse_name);

    $api_url = "https://api.kameone.cl/api/Inventario/getStockBodega/{$warehouse_encoded}";

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        error_log('KAME ERP: Error al obtener el stock de la bodega ' . $warehouse_name);
        error_log($response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($status_code == 200) {
        $data = json_decode($body, true);
        return $data;
    } else {
        error_log('KAME ERP: Error al obtener el stock de la bodega ' . $warehouse_name);
        error_log('Código de estado: ' . $status_code);
        error_log('Respuesta: ' . $body);
        return false;
    }
}

function kame_erp_synchronize_inventory() {
    // Verificar si la sincronización está habilitada
    $sync_enabled = get_option('kame_erp_enable_sync', true);
    if (!$sync_enabled) {
        return;
    }

    $warehouses = get_option('kame_erp_warehouses', array());
    if (!is_array($warehouses) || empty($warehouses)) {
        error_log('KAME ERP: No hay bodegas configuradas para la sincronización de inventario.');
        return;
    }

    // Obtener todos los productos de WooCommerce con SKU
    $args = array(
        'status' => 'publish',
        'limit'  => -1,
        'sku'    => '',
    );

    $products = wc_get_products($args);

    // Crear un mapa de productos por SKU y almacenar la bodega asociada
    $products_by_sku = array();
    foreach ($products as $product) {
        $sku = $product->get_sku();
        if (!empty($sku)) {
            $warehouse = get_post_meta($product->get_id(), '_kame_erp_warehouse', true);
            $products_by_sku[$sku] = array(
                'product' => $product,
                'warehouse' => $warehouse,
            );
        }
    }

    // Obtener el stock de cada bodega y actualizar los productos correspondientes
    foreach ($warehouses as $warehouse) {
        $stock_data = kame_erp_get_stock_by_warehouse($warehouse);
        if ($stock_data !== false && is_array($stock_data)) {
            foreach ($stock_data as $item) {
                $sku = isset($item['NombreArticulo']) ? $item['NombreArticulo'] : '';
                $stock = isset($item['Cantidad']) ? floatval($item['Cantidad']) : 0;

                if (!empty($sku) && isset($products_by_sku[$sku])) {
                    $product_info = $products_by_sku[$sku];
                    // Verificar si la bodega del producto coincide
                    if ($product_info['warehouse'] == $warehouse) {
                        $product = $product_info['product'];
                        $product->set_stock_quantity($stock);
                        $product->save();

                        // Marcar el producto como sincronizado
                        update_post_meta($product->get_id(), '_kame_erp_synced', 'yes');
                    }
                }
            }
        }
    }

    // Marcar productos no sincronizados
    foreach ($products_by_sku as $sku => $product_info) {
        if (!get_post_meta($product_info['product']->get_id(), '_kame_erp_synced', true)) {
            // Producto no sincronizado
            update_post_meta($product_info['product']->get_id(), '_kame_erp_synced', 'no');
        }
    }

    // Actualizar la fecha y hora de la última sincronización
    update_option('kame_erp_last_sync_time', current_time('timestamp'));
}

// Programar la tarea de sincronización
function kame_erp_schedule_inventory_sync() {
    // Primero, eliminar cualquier evento programado previamente
    wp_clear_scheduled_hook('kame_erp_inventory_sync_event');

    // Programar un nuevo evento según la frecuencia configurada
    $frequency = get_option('kame_erp_sync_frequency', 60); // Valor predeterminado de 60 minutos
    if ($frequency > 0) {
        wp_schedule_event(time(), 'kame_erp_custom_interval', 'kame_erp_inventory_sync_event');
    }
}
add_action('admin_init', 'kame_erp_schedule_inventory_sync');

function kame_erp_add_custom_intervals($schedules) {
    $frequency = get_option('kame_erp_sync_frequency', 60); // Valor predeterminado de 60 minutos
    $interval_in_seconds = $frequency * 60;

    $schedules['kame_erp_custom_interval'] = array(
        'interval' => $interval_in_seconds,
        'display'  => __('Cada ' . $frequency . ' minutos')
    );
    return $schedules;
}
add_filter('cron_schedules', 'kame_erp_add_custom_intervals');

add_action('kame_erp_inventory_sync_event', 'kame_erp_synchronize_inventory');

// =====================
// 9. IMPORTACIÓN DE PRODUCTOS DESDE KAME ERP
// =====================

function kame_erp_import_products() {
    // Obtener el token de acceso
    $access_token = kame_erp_get_access_token();
    if (!$access_token) {
        error_log('KAME ERP: No se pudo obtener el token de acceso para la importación de productos.');
        return;
    }

    // Endpoint de la API para obtener productos
    $api_url = 'https://api.kameone.cl/api/Articulo/getArticulos';

    // Obtener los productos desde KAME ERP
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) {
        error_log('KAME ERP: Error al obtener los productos para la importación.');
        error_log($response->get_error_message());
        return;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($status_code == 200) {
        $products_data = json_decode($body, true);

        if (is_array($products_data)) {
            foreach ($products_data as $product_data) {
                // Extraer la información necesaria
                $sku = isset($product_data['Codigo']) ? $product_data['Codigo'] : '';
                $name = isset($product_data['Nombre']) ? $product_data['Nombre'] : '';
                $description = isset($product_data['Descripcion']) ? $product_data['Descripcion'] : '';
                $price = isset($product_data['Precio']) ? floatval($product_data['Precio']) : 0.0;
                $warehouse = isset($product_data['Bodega']) ? $product_data['Bodega'] : '';

                // Verificar si el producto ya existe en WooCommerce
                $existing_product_id = wc_get_product_id_by_sku($sku);
                if ($existing_product_id) {
                    // Actualizar el producto existente
                    $product = wc_get_product($existing_product_id);
                    $product->set_name($name);
                    $product->set_description($description);
                    $product->set_regular_price($price);
                    $product->save();

                    // Actualizar la bodega asociada
                    update_post_meta($product->get_id(), '_kame_erp_warehouse', $warehouse);
                } else {
                    // Crear un nuevo producto
                    $product = new WC_Product_Simple();
                    $product->set_name($name);
                    $product->set_description($description);
                    $product->set_sku($sku);
                    $product->set_regular_price($price);
                    $product->set_manage_stock(true);
                    $product->save();

                    // Asignar la bodega
                    update_post_meta($product->get_id(), '_kame_erp_warehouse', $warehouse);
                }
            }
        }
    } else {
        error_log('KAME ERP: Error en la API al obtener los productos para la importación.');
        error_log('Código de estado: ' . $status_code);
        error_log('Respuesta: ' . $body);
    }
}

// =====================
// 10. AÑADIR COLUMNA EN LA LISTA DE PRODUCTOS
// =====================

add_filter('manage_edit-product_columns', 'kame_erp_add_product_columns');
function kame_erp_add_product_columns($columns) {
    $columns['kame_erp_synced'] = 'KAME ERP';
    return $columns;
}

add_action('manage_product_posts_custom_column', 'kame_erp_product_column_content', 10, 2);
function kame_erp_product_column_content($column, $post_id) {
    if ($column == 'kame_erp_synced') {
        $synced = get_post_meta($post_id, '_kame_erp_synced', true);
        if ($synced == 'yes') {
            echo '<span class="kame-erp-synced-indicator kame-erp-synced-yes" title="Producto sincronizado"></span>';
        } else {
            echo '<span class="kame-erp-synced-indicator kame-erp-synced-no" title="Producto no sincronizado"></span>';
        }
    }
}

add_action('admin_head', 'kame_erp_product_column_styles');
function kame_erp_product_column_styles() {
    ?>
    <style>
        .kame-erp-synced-indicator {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }

        .kame-erp-synced-yes {
            background-color: #4CAF50; /* Verde */
        }

        .kame-erp-synced-no {
            background-color: #f44336; /* Rojo */
        }
    </style>
    <?php
}

// =====================
// 11. GENERACIÓN DE README.TXT CON EL CHANGELOG
// =====================

function kame_erp_generate_readme() {
    $readme_content = <<<EOT
=== Kame ERP - WooCommerce Integration ===
Contributors: Agartha Marketing Agency
Tags: woocommerce, kame erp, integration, inventory, sales
Requires at least: 4.0
Tested up to: 6.0
Stable tag: 2.7.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Este plugin permite integrar WooCommerce con KAME ERP, incluyendo sincronización de inventario, envío de ventas, y más.

== Description ==

Este plugin conecta tu tienda WooCommerce con KAME ERP, permitiendo la sincronización de inventario, el envío de datos de venta, y la gestión de bodegas.

== Changelog ==

= 2.7.8 =
* Reordenadas las funciones para evitar errores de llamadas a funciones indefinidas.
* Corrección de errores críticos reportados.

= 2.7.7 =
* Eliminada la etiqueta de cierre ?> para prevenir problemas con las cabeceras.

= 2.7.6 =
* Añadido campo de bodega en la ficha de producto.
* Implementada importación inicial de productos desde KAME ERP.
* Generación automática de readme.txt con changelog.

== Installation ==

1. Sube el plugin a tu directorio de plugins y actívalo.
2. Configura tus credenciales de KAME ERP en la página de configuración.
3. Ajusta las funciones que deseas habilitar en la página de funciones.
4. Realiza una importación inicial de productos si es necesario.

== Frequently Asked Questions ==

= ¿Cómo configuro las credenciales de KAME ERP? =
Ve a la página "Configuración KAME ERP" en el menú del plugin y completa los campos requeridos.

EOT;

    file_put_contents(plugin_dir_path(__FILE__) . 'readme.txt', $readme_content);
}

// Generar el readme.txt al activar el plugin
register_activation_hook(__FILE__, 'kame_erp_generate_readme');

// =====================
// 12. MOSTRAR ESTADO DE CONEXIÓN EN LA ADMIN BAR
// =====================
add_action('admin_bar_menu', 'kame_erp_admin_bar_status', 100);
function kame_erp_admin_bar_status($admin_bar) {
    $is_connected = kame_erp_check_connection();
    $status_text = $is_connected ? 'Online' : 'Offline';
    $status_class = $is_connected ? 'kame-erp-status-online' : 'kame-erp-status-offline';

    $admin_bar->add_node(array(
        'id'    => 'kame_erp_connection_status',
        'title' => 'Conexión KAME ERP: <span class="' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span>',
        'meta'  => array(
            'title' => 'Estado de la conexión con KAME ERP'
        ),
    ));
}

// =====================
// 13. ESTILOS PERSONALIZADOS
// =====================

add_action('admin_head', 'kame_erp_admin_bar_styles');
function kame_erp_admin_bar_styles() {
    ?>
    <style>
        .kame-erp-status-online {
            background-color: #4CAF50;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
        }

        .kame-erp-status-offline {
            background-color: #f44336;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
        }
    </style>
    <?php
}

// Asegúrate de que no haya código o espacios en blanco después de esta línea

// Verificar la validez del token y renovarlo si es necesario
function kame_erp_check_and_refresh_token() {
    $access_token = get_option('kame_erp_access_token');
    $token_expiration = get_option('kame_erp_token_expiration');

    // Si no hay token o ha expirado, solicitar uno nuevo
    if (!$access_token || time() >= $token_expiration) {
        $new_token = kame_erp_get_access_token();
        if ($new_token) {
            return $new_token;
        } else {
            kame_erp_log_error('KAME ERP: No se pudo renovar el token de acceso.');
            return false;
        }
    }

    return $access_token;
}

// Registrar errores en un archivo dentro de la carpeta del plugin
function kame_erp_log_error($message) {
    $file_path = plugin_dir_path(__FILE__) . 'kame-erp-error-log.txt';

    // Crear el archivo si no existe
    if (!file_exists($file_path)) {
        $log_header = "KAME ERP Error Log\n===================\n";
        file_put_contents($file_path, $log_header, LOCK_EX);
    }

    // Agregar el mensaje al archivo de log
    $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($file_path, $log_message, FILE_APPEND | LOCK_EX);
}


// Registrar las opciones para Access Token y su vencimiento
register_setting('kame_erp_settings', 'kame_erp_access_token', 'sanitize_text_field');
register_setting('kame_erp_settings', 'kame_erp_token_expires_in', 'intval');

// Callback para mostrar el Access Token en el panel de administración
function kame_erp_access_token_callback() {
    $access_token = get_option('kame_erp_access_token', '');
    echo '<input type="text" name="kame_erp_access_token" value="' . esc_attr($access_token) . '" style="width: 100%;" readonly>';
}

// Guardar el Access Token desde la respuesta de la API
function update_kame_erp_access_token($token_response) {
    if (!empty($token_response)) {
        // Decodificar la respuesta JSON
        $token_data = json_decode($token_response, true);

        if (isset($token_data['access_token'])) {
            update_option('kame_erp_access_token', sanitize_text_field($token_data['access_token']));
        }

        if (isset($token_data['expires_in'])) {
            update_option('kame_erp_token_expires_in', intval($token_data['expires_in']));
        }
    }
}

// Ejemplo de uso: obtener y guardar el Access Token (adaptar según la función existente)
function fetch_and_store_kame_erp_access_token() {
    // Aquí debería llamarse la función que obtiene el token desde la API
    $response = your_function_to_get_access_token(); // Reemplazar con la función real
    update_kame_erp_access_token($response);
}
