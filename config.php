<?php
if(!isset($_SESSION))
    session_start(); // Inicia la sesión

// URL de la API de CoinGecko
$api_url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,ripple&vs_currencies=usd';

// Inicializa variables si no existen
if (!isset($_SESSION['saldo'])) {
    $_SESSION['saldo'] = 100000; // Saldo inicial
    $_SESSION['criptos'] = ['BTC' => 0, 'ETH' => 0, 'XRP' => 0];
    $_SESSION['precios'] = ['BTC' => 0, 'ETH' => 0, 'XRP' => 0];
    $_SESSION['historico'] = ['BTC' => [], 'ETH' => [], 'XRP' => []];
}

// Evita peticiones innecesarias: Actualiza cada 10 segundos
if (!isset($_SESSION['ultima_actualizacion']) || (time() - $_SESSION['ultima_actualizacion']) >= 10) {
    $response = @file_get_contents($api_url);
    $data = json_decode($response, true);

    if ($response !== false && $data) {
        foreach (['BTC' => 'bitcoin', 'ETH' => 'ethereum', 'XRP' => 'ripple'] as $cripto => $api_id) {
            if (isset($data[$api_id]['usd'])) {
                $_SESSION['historico'][$cripto][] = $_SESSION['precios'][$cripto]; // Guarda precio anterior
                $_SESSION['precios'][$cripto] = $data[$api_id]['usd']; // Nuevo precio desde API

                // Mantiene un historial de 10 registros
                if (count($_SESSION['historico'][$cripto]) > 10) {
                    array_shift($_SESSION['historico'][$cripto]); // Elimina el más antiguo
                }
            }
        }
        $_SESSION['ultima_actualizacion'] = time(); // Actualiza el tiempo
    }
}

// Manejo de compra
if (isset($_POST['comprar'])) {
    $cripto = $_POST['cripto'] ?? null;
    $cantidad = floatval($_POST['cantidad'] ?? 0);

    if ($cripto && $cantidad > 0 && isset($_SESSION['precios'][$cripto])) {
        $costoTotal = $_SESSION['precios'][$cripto] * $cantidad;
        if ($_SESSION['saldo'] >= $costoTotal) {
            $_SESSION['saldo'] -= $costoTotal;
            $_SESSION['criptos'][$cripto] += $cantidad;
        }
    }
}

// Manejo de venta
if (isset($_POST['vender'])) {
    $cripto = $_POST['cripto'] ?? null;
    $cantidad = floatval($_POST['cantidad'] ?? 0);

    if ($cripto && $cantidad > 0 && isset($_SESSION['criptos'][$cripto]) && $_SESSION['criptos'][$cripto] >= $cantidad) {
        $_SESSION['saldo'] += $_SESSION['precios'][$cripto] * $cantidad;
        $_SESSION['criptos'][$cripto] -= $cantidad;
    }
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($_SESSION['precios']);
    exit();
}

?>
