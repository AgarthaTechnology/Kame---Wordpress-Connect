<?php
function kame_erp_get_stock_by_warehouse($warehouse_name) {
    // Get stock by warehouse logic
}

function kame_erp_synchronize_inventory() {
    $products = wc_get_products(array('limit' => -1)); // Obtener todos los productos de WooCommerce

    foreach ($products as $product) {
        $sku = $product->get_sku();
        if ($sku) {
            $url = 'https://api.kameone.cl/api/Inventario/getStockArticulo/' . urlencode($sku);

            // Inicializar cURL
            $ch = curl_init();

            // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . get_option('kame_erp_access_token'),
                'Content-Type: application/json'
            ]);

            // Ejecutar la solicitud y obtener la respuesta
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Cerrar cURL
            curl_close($ch);

            // Manejar la respuesta
            if ($http_code === 200) {
                $data = json_decode($response, true);
                if ($data && isset($data['precioVentaNeto']) && isset($data['saldo'])) {
                    // Actualizar el precio y el stock del producto
                    $product->set_regular_price($data['precioVentaNeto']);
                    $product->set_stock_quantity($data['saldo']);
                    $product->save();
                } else {
                    // Manejar errores de datos de inventario
                    error_log('Error en los datos de inventario para el producto SKU: ' . $sku);
                }
            } else {
                // Manejar errores de solicitud HTTP
                error_log('Error al obtener datos de inventario: HTTP ' . $http_code . ' para el producto SKU: ' . $sku);
            }
        }
    }
}
