<?php
/*
Plugin Name: Kame ERP - WooCommerce Integration
Description: Configura las credenciales de la API de KAME ERP desde el panel de administrador de WordPress. Incluye integración con el checkout de WooCommerce, envío de datos de venta al ERP, sincronización de inventario, y gestión de bodegas. Diseñado por Agartha Marketing Agency.
Version: 3.4.5
Author: Agartha Marketing Agency
Author URI: https://agarthamarketing.com
*/

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/inventory.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/import-products.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend/checkout.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/connection.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/sync.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils/log.php';
require_once plugin_dir_path(__FILE__) . 'includes/cron/schedule.php';

// Initialize plugin functions and hooks
function kame_erp_init() {
    kame_erp_settings_init();
    kame_erp_functions_settings_init();
    kame_erp_inventory_settings_init();
    kame_erp_schedule_inventory_sync();
}
add_action('admin_init', 'kame_erp_init');
add_action('admin_menu', 'kame_erp_menu');

// Add settings link on plugin page
function kame_erp_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=kame_erp_settings">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'kame_erp_settings_link');

add_action('admin_notices', 'kame_erp_check_woocommerce');

function kame_erp_check_woocommerce() {
    // Verificar si WooCommerce está activo
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        // Mostrar notificación
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>El plugin KAME ERP requiere <strong>WooCommerce</strong> para funcionar. <a href="' . esc_url(admin_url('plugin-install.php?s=WooCommerce&tab=search&type=term')) . '">Instalar WooCommerce</a></p>';
        echo '</div>';
    }
}
