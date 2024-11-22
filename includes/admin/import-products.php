<?php

// Function to manually import products from KAME ERP to WooCommerce
function kame_erp_import_products() {
    // Here would go the logic for manually importing products from the KAME ERP API and creating them in WooCommerce
}

// Add the option in WordPress admin to manually trigger the product import
function kame_erp_import_products_menu() {
    add_menu_page(
        'Importar/Exportar Kame-Woocommerce',
        'Importar Productos',
        'manage_options',
        'kame_erp_import_products',
        'kame_erp_import_products_page'
    );
}
add_action('admin_menu', 'kame_erp_import_products_menu');

function kame_erp_import_products_page() {
    ?>
    <div class="wrap">
        <h1>Importar/Exportar Kame-Woocommerce</h1>
        <p>Esta sección te permite importar productos desde KAME ERP a WooCommerce.</p>
        <p><strong>AVISO:</strong> Esta acción es irreversible y solo creará sus productos totales de Kame ERP en WooCommerce.</p>
        <button id="import_kame_products" class="button button-primary">IMPORTAR DESDE KAME ERP</button>
    </div>

    <script type="text/javascript">
        document.getElementById('import_kame_products').onclick = function() {
            if (confirm('AVISO: Esta acción es irreversible y solo creará sus productos totales de Kame ERP en WooCommerce. ¿Desea continuar?')) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=import_products_from_kame'
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Productos importados exitosamente!');
                    } else {
                        alert('Error al importar productos: ' + data.message);
                    }
                });
            }
        };
    </script>
    <?php
}

function kame_erp_import_products_ajax_handler() {
    try {
        kame_erp_import_products();
        wp_send_json_success();
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_import_products_from_kame', 'kame_erp_import_products_ajax_handler');
?>
