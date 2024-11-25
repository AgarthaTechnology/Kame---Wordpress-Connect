<?php
function kame_erp_synchronize_inventory() {
    // Obtén el token de acceso y verifica su validez
    $access_token = get_option('kame_erp_access_token');
    $token_expiration = (int) get_option('kame_erp_token_expiration', 0);

    if (empty($access_token) || time() >= $token_expiration) {
        kame_erp_log_error('El token de acceso no está disponible o ha caducado.');
        return;
    }

    $products = wc_get_products(array('limit' => -1)); // Obtener todos los productos de WooCommerce

    foreach ($products as $product) {
        $sku = $product->get_sku();
        if ($sku) {
            // URL correcta para conectar a la API
            $url = 'https://api.kameone.cl/api/Inventario/getStockArticulo/' . $sku;

            // Inicializar cURL
            $ch = curl_init();

            // Configurar opciones de cURL
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ],
            ]);

            // Ejecutar la solicitud y obtener la respuesta
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);

            // Cerrar cURL
            curl_close($ch);

            // Manejar la respuesta
            if ($http_code === 200) {
                $data = json_decode($response, true);
                if ($data && is_array($data) && !empty($data)) {
                    $item = $data[0]; // Acceder al primer elemento del array
                    if (isset($item['precioVentaNeto']) && isset($item['saldo'])) {
                        // Actualizar el precio y el stock del producto
                        $product->set_regular_price($item['precioVentaNeto']);
                        $product->set_stock_quantity($item['saldo']);
                        $product->save();
                    } else {
                        kame_erp_log_error('Datos de inventario incompletos para el producto SKU: ' . $sku . '. Respuesta: ' . $response);
                    }
                } else {
                    kame_erp_log_error('Respuesta de inventario inválida para el producto SKU: ' . $sku . '. Respuesta: ' . $response);
                }
            } else {
                // Manejar errores de solicitud HTTP
                kame_erp_log_error('Error al obtener datos de inventario: HTTP ' . $http_code . ' para el producto SKU: ' . $sku . '. Error cURL: ' . $curl_error);
            }
        }
    }
}
