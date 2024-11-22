<?php
function kame_erp_import_products_settings_init() {
    add_settings_section(
        'kame_erp_import_products_section',
        'Configuración de Importación de Productos',
        'kame_erp_import_products_section_callback',
        'kame_erp_import_products'
    );

    add_settings_field(
        'kame_erp_import_frequency',
        'Frecuencia de Importación',
        'kame_erp_import_frequency_callback',
        'kame_erp_import_products',
        'kame_erp_import_products_section'
    );

    register_setting('kame_erp_import_products', 'kame_erp_import_frequency');
}

function kame_erp_import_products_section_callback() {
    echo '<p>Configura las opciones de importación de productos.</p>';
}

function kame_erp_import_frequency_callback() {
    $frequency = get_option('kame_erp_import_frequency', 'daily');
    echo '<select name="kame_erp_import_frequency">
            <option value="hourly" ' . selected($frequency, 'hourly', false) . '>Cada hora</option>
            <option value="twicedaily" ' . selected($frequency, 'twicedaily', false) . '>Dos veces al día</option>
            <option value="daily" ' . selected($frequency, 'daily', false) . '>Diariamente</option>
          </select>';
}

function kame_erp_import_export_page() {
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
        import_products_from_kame();
        wp_send_json_success();
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_import_products_from_kame', 'kame_erp_import_products_ajax_handler');

add_action('admin_init', 'kame_erp_import_products_settings_init');
