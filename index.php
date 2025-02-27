<?php
session_start();
include('config.php');

// Obtener precios desde la sesión
$precios = $_SESSION['precios'] ?? [
    'BTC' => 0,
    'ETH' => 0,
    'XRP' => 0
];

// Procesar compra de criptomonedas
if (isset($_POST['comprar'])) {
    $cripto = htmlspecialchars($_POST['cripto'] ?? '');
    $cantidad = floatval($_POST['cantidad'] ?? 0);
    $precio = $precios[$cripto] ?? 0;
    
    if ($cantidad > 0 && $precio > 0 && isset($_SESSION['saldo']) && $_SESSION['saldo'] >= $cantidad * $precio) {
        $_SESSION['saldo'] -= $cantidad * $precio;
        $_SESSION['criptos'][$cripto] += $cantidad;
        $_SESSION['historico'][$cripto][] = $precio;
    }
}

// Procesar venta de criptomonedas
if (isset($_POST['vender'])) {
    $cripto = htmlspecialchars($_POST['cripto'] ?? '');
    $cantidad = floatval($_POST['cantidad'] ?? 0);
    $precio = $precios[$cripto] ?? 0;

    if ($cantidad > 0 && isset($_SESSION['criptos'][$cripto]) && $_SESSION['criptos'][$cripto] >= $cantidad) {
        $_SESSION['saldo'] += $cantidad * $precio;
        $_SESSION['criptos'][$cripto] -= $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Criptomonedas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function actualizarPrecios() {
            fetch('actualizar.php')
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        for (var cripto in data) {
                            document.getElementById(`precio-${cripto}`).innerText = `$${data[cripto].toFixed(2)}`;
                        }
                    }
                });
        }
        //Updating chart on function call with update() method
        
        setInterval(actualizarPrecios, 10000);
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1>Simulador de Criptomonedas</h1>
        <div class="alert alert-info">
            <strong>Saldo disponible: </strong> $<?php echo number_format($_SESSION['saldo'], 2); ?>
        </div>
        <h2>Criptomonedas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Criptomoneda</th>
                    <th>Precio</th>
                    <th>Cantidad en tu cuenta</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($precios as $cripto => $precio): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cripto); ?></td>
                        <td id="precio-<?php echo $cripto; ?>">$<?php echo number_format($precio, 2); ?></td>
                        <td><?php echo $_SESSION['criptos'][$cripto] ?? 0; ?></td>
                        <td>
                            <form action="" method="POST" class="d-inline">
                                <input type="number" name="cantidad" placeholder="Cantidad" min="0.00001" step="0.00001" required>
                                <input type="hidden" name="cripto" value="<?php echo htmlspecialchars($cripto); ?>">
                                <button type="submit" name="comprar" class="btn btn-success">Comprar</button>
                            </form>
                            <form action="" method="POST" class="d-inline">
                                <input type="number" name="cantidad" placeholder="Cantidad" min="0.00001" step="0.00001" required>
                                <input type="hidden" name="cripto" value="<?php echo htmlspecialchars($cripto); ?>">
                                <button type="submit" name="vender" class="btn btn-danger">Vender</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="mt-5">Gráfico de Evolución del Precio</h2>

        <!-- Selección de criptomoneda -->
         <form method="GET">
            <select name="cripto_elegida" class="form-select" onchange="this.form.submit()">
                <option value="">Selecciona una criptomoneda</option>
                <option value="BTC" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'BTC' ? 'selected' : ''; ?>>BTC</option>
                <option value="ETH" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'ETH' ? 'selected' : ''; ?>>ETH</option>
                <option value="XRP" <?php echo isset($_GET['cripto_elegida']) && $_GET['cripto_elegida'] == 'XRP' ? 'selected' : ''; ?>>XRP</option>
            </select>
        </form>

        <canvas id="cryptoChart" width="400" height="200"></canvas>
        <script>
            <?php $cripto_elegida = $_GET['cripto_elegida']; $_SESSION['cripto_elegida'] = $cripto_elegida; ?>
            var ctx = document.getElementById('cryptoChart').getContext('2d');
            var historial = <?php echo json_encode($_SESSION['historico'][$cripto_elegida] ?? []); ?>;
            let chartCrypto = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: historial.map((_, i) => `Punto ${i+1}`),
                    datasets: [{
                        label: 'Precio de <?php echo $cripto_elegida ?>(USD)',
                        data: historial,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        fill: false,
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: false } }
                }
            });
        </script>
        <script>
function actualizarPrecios() {
    fetch('actualizar.php')
        .then(response => response.json())
        .then(data => {
            Object.keys(data).forEach(cripto => {
                document.getElementById(`precio-${cripto}`).innerText = `$${data[cripto].toLocaleString()}`;
            });
        })
        .catch(error => console.error("Error al actualizar precios:", error));
}
// Actualizar cada 10 segundos
setInterval(actualizarPrecios, 10000);

function UpdateChart() {
    let newData;
    const req = new Request('updateChart.php');
    fetch(req)
    .then(console.log('funciona'))
    .then((response) => response.json())
    .then(_data =>{
        console.log(_data.historico);
        chartCrypto.data.datasets[0].data = _data.historico;
        chartCrypto.update();
    });
    
}
setInterval(UpdateChart, 10000);
</script>
    </div>
</body>
</html>
