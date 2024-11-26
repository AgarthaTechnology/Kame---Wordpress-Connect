<?php
// Incluir el archivo de conexión para manejar el token
require_once __DIR__ . '/connection.php';

function kame_erp_get_stock_by_warehouse($warehouse_name) {
    // Aquí puedes incluir la lógica si es necesaria
}

function kame_erp_synchronize_inventory() {
    echo "Función de sincronización iniciada<br>";

    // Obtener todos los productos de WooCommerce
    $products = wc_get_products(array('limit' => -1));

    if (!$products || empty($products)) {
        echo "No se encontraron productos para sincronizar.<br>";
        error_log('No se encontraron productos para sincronizar.');
        return;
    }

    echo "Total de productos encontrados: " . count($products) . "<br>";

    // Obtener el token de acceso
    $access_token = get_option('kame_erp_access_token');
    $token_expiration = get_option('kame_erp_token_expiration', 0);

    // Verificar si el token ha expirado
    if (time() >= $token_expiration) {
        echo "El token ha expirado, obteniendo uno nuevo...<br>";
        $token_result = fetch_and_store_kame_erp_access_token();

        if (!$token_result['success']) {
            echo "Error al obtener el token de acceso: " . $token_result['message'] . "<br>";
            error_log("Error al obtener el token de acceso: " . $token_result['message']);
            return;
        } else {
            echo "Nuevo token de acceso obtenido.<br>";
        }

        // Obtener el nuevo token
        $access_token = get_option('kame_erp_access_token');
        $token_expiration = get_option('kame_erp_token_expiration', 0);
    } else {
        echo "Token de acceso válido encontrado.<br>";
    }

    foreach ($products as $product) {
        $sku = $product->get_sku();
        if ($sku) {
            echo "Procesando producto con SKU: $sku<br>";

            $url = 'https://api.kameone.cl/api/Inventario/getStockArticulo/' . urlencode($sku);

            // Definir una función para realizar la solicitud a la API
            $attempt_request = function() use ($url, &$access_token, $sku) {
                $ch = curl_init();

                // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $access_token,
                    'Content-Type: application/json'
                ]);

                // Ejecutar la solicitud y obtener la respuesta
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($response === false) {
                    $curl_error = curl_error($ch);
                    echo "Error en cURL al procesar SKU $sku: $curl_error<br>";
                    error_log("Error en cURL al procesar SKU $sku: $curl_error");
                    curl_close($ch);
                    return false;
                }

                // Cerrar cURL
                curl_close($ch);

                return array('http_code' => $http_code, 'response' => $response);
            };

            // Realizar la solicitud inicial
            $result = $attempt_request();

            // Si obtenemos un error 401, intentamos obtener un nuevo token y reintentar
            if ($result['http_code'] === 401) {
                echo "Token de acceso expirado o inválido, obteniendo uno nuevo...<br>";
                $token_result = fetch_and_store_kame_erp_access_token();

                if (!$token_result['success']) {
                    echo "Error al obtener el token de acceso: " . $token_result['message'] . "<br>";
                    error_log("Error al obtener el token de acceso: " . $token_result['message']);
                    continue;
                } else {
                    echo "Nuevo token de acceso obtenido.<br>";
                }

                // Obtener el nuevo token
                $access_token = get_option('kame_erp_access_token');
                if (!$access_token) {
                    echo "No se pudo obtener un nuevo token de acceso.<br>";
                    error_log("No se pudo obtener un nuevo token de acceso.");
                    continue;
                }

                // Reintentar la solicitud con el nuevo token
                $result = $attempt_request();
            }

            // Manejar la respuesta
            if ($result['http_code'] === 200) {
                $data = json_decode($result['response'], true);
                if ($data && isset($data[0])) {
                    $product_data = $data[0];

                    // Obtener precio y stock
                    $precio = isset($product_data['precioVentaNeto']) ? floatval($product_data['precioVentaNeto']) : null;
                    $saldo = isset($product_data['saldo']) ? $product_data['saldo'] : null;

                    // Actualizar el precio si está disponible
                    if ($precio !== null) {
                        echo "Actualizando precio del producto SKU $sku: Precio - $precio<br>";
                        $product->set_regular_price($precio);
                    } else {
                        echo "Precio no disponible para SKU $sku<br>";
                    }

                    // Actualizar el stock, estableciendo en 0 si saldo es null
                    if ($saldo !== null) {
                        echo "Actualizando stock del producto SKU $sku: Stock - $saldo<br>";
                        $stock = intval($saldo);
                        $product->set_stock_quantity($stock);
                    } else {
                        echo "Saldo no disponible para SKU $sku, estableciendo stock en 0<br>";
                        $product->set_stock_quantity(0);
                    }

                    // Guardar los cambios en el producto
                    $product->save();
                } else {
                    echo "Datos de inventario incompletos para SKU $sku<br>";
                    echo "Respuesta de la API: " . htmlspecialchars($result['response']) . "<br>";
                    error_log("Error en los datos de inventario para el producto SKU: $sku. Respuesta: {$result['response']}");
                }
            } else {
                echo "Error HTTP {$result['http_code']} al obtener datos de inventario para SKU $sku<br>";
                echo "Respuesta de la API: " . htmlspecialchars($result['response']) . "<br>";
                error_log("Error al obtener datos de inventario: HTTP {$result['http_code']} para el producto SKU: $sku. Respuesta: {$result['response']}");
            }
        } else {
            echo "Producto con ID {$product->get_id()} sin SKU, omitido.<br>";
        }
    }

    echo "Función de sincronización completada.<br>";
}
?>
