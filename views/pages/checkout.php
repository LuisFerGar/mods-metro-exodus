<?php
// Garante que a sessão seja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa la mensaje de estado
$status_message = 'Esperando procesamiento...';
$status_type = 'info';

// Usamos 'id_usuario' (español) para coincidir con tu Login
$usuario_id = $_SESSION['id_usuario'] ?? null; 

if (!$usuario_id) {
    $_SESSION['checkout_status'] = ['message' => 'Error: Sesión no encontrada. Por favor, inicia sesión.', 'type' => 'danger'];
    header('Location: index.php?pagina=login'); 
    exit();
}

// 1. Verifica si los datos de venta están en la sesión
if (!isset($_SESSION['dados_venda']) || empty($_SESSION['dados_venda']['produtos'])) {
    header('Location: index.php?pagina=carrito');
    exit();
}

$dados_venda = $_SESSION['dados_venda'];
$total = $dados_venda['total'];
$produtos_json = json_encode($dados_venda['produtos']);

// 2. URL de la API de Venda (Ajusta si tu carpeta se llama diferente)
$api_url = 'http://localhost/mods-metro-exodus/app/controller/api_realizarventa.php';

// 3. Prepara el payload
$payload = http_build_query([
    'total' => $total,
    'produtos_json' => $produtos_json,
    'usuario_id' => $usuario_id, 
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $payload,
        'timeout' => 30 
    ],
];
$context  = stream_context_create($options);
$json_data = @file_get_contents($api_url, false, $context);

// 4. Procesa la respuesta
if ($json_data === false) {
    $status_message = 'Error: Fallo al conectar con el servidor de comercio (API).';
    $status_type = 'danger';
} else {
    $data = json_decode($json_data, true);
    
    if (isset($data['status']) && $data['status'] === 'success') {
        $api_message = $data['message'] ?? 'Compra Finalizada con Éxito.';
        $status_message = '¡Transacción Aprobada! ' . htmlspecialchars($api_message);
        $status_type = 'success';
        
        // --- AQUÍ ESTÁ LA MAGIA PARA VACIAR EL CARRITO ---
        unset($_SESSION['dados_venda']); // Borra los datos temporales de venta
        unset($_SESSION['carrito']);     // <--- ESTO BORRA LOS PRODUCTOS DEL CARRITO Y PONE EL CONTADOR EN 0
        
    } else {
        $status_message = 'Error al procesar la compra: ' . ($data['message'] ?? 'Respuesta desconocida.');
        $status_type = 'danger';
    }
}
?>

<div class="container py-5">
    <h2 class="display-5 text-warning fw-bold mb-5 text-center">Procesando Transacción</h2>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg p-5 text-center">
                <div class="mb-4">
                    <?php if ($status_type === 'success'): ?>
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-success">¡Transacción Aprobada!</h3>
                        
                        <p class="text-light fs-5 mt-3"><?php echo htmlspecialchars($status_message); ?></p> 
                        <p class="text-secondary">Tu nuevo equipo está listo para descarga. Revisa tu frecuencia de radio (email).</p>
                        
                        <a href="index.php?pagina=mods" class="btn btn-warning btn-lg mt-3 fw-bold">Volver al Catálogo</a>
                    
                    <?php elseif ($status_type === 'danger'): ?>
                        <i class="bi bi-x-octagon-fill text-danger" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-danger">¡Fallo en la Compra!</h3>
                        
                        <p class="text-light fs-5 mt-3"><?php echo htmlspecialchars($status_message); ?></p>
                        <p class="text-secondary">Por favor, regresa al carrito e intenta nuevamente.</p>
                        
                        <a href="index.php?pagina=carrito" class="btn btn-outline-warning btn-lg mt-3">Volver al Carrito</a>
                    
                    <?php else: ?>
                        <i class="bi bi-hourglass-split text-info" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-info">Procesando...</h3>
                        <p class="text-light fs-5"><?php echo htmlspecialchars($status_message); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>