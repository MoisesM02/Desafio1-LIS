<?php
header('Content-Type: application/json');
if(!isset($_SESSION))
    session_start();

    $api_url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,ripple&vs_currencies=usd';
    $response = @file_get_contents($api_url);
    $data = json_decode($response, true);
    $cripto_elegida = $_SESSION['cripto_elegida'];
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
        
    }

$json = ['historico' => $_SESSION['historico'][$cripto_elegida]];
     echo json_encode($json ?? []); 

?>