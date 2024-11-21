<?php
/*
Plugin Name: Kame ERP - WooCommerce Integration
Description: Integrates WooCommerce with Kame ERP.
Version: 1.0
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
