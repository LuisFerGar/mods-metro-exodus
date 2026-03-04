<?php
// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$status_message = 'Esperando procesamiento...';
$status_type = 'info';

// Detectamos el nombre de usuario de la sesión (Anton123, etc.)
$usuario_id = $_SESSION['id_usuario'] ?? $_SESSION['usuario_logueado'] ?? $_SESSION['usuario'] ?? null; 

if (!$usuario_id) {
    header('Location: index.php?pagina=login'); 
    exit();
}

// Verifica si los datos de venta están en la sesión
if (!isset($_SESSION['dados_venda']) || empty($_SESSION['dados_venda']['produtos'])) {
    header('Location: index.php?pagina=carrito');
    exit();
}

$produtos = $_SESSION['dados_venda']['produtos'];

// --- CONEXIÓN DIRECTA Y GUARDADO EN BASE DE DATOS (Sin APIs) ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario_db = "root";
$contrasena_db = "";

$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

if ($mysqli->connect_error) {
    $status_message = 'Error crítico: No se pudo conectar a la base de datos central.';
    $status_type = 'danger';
} else {
    // Iniciamos la transacción (para que no se guarde a medias)
    $mysqli->begin_transaction();

    try {
        // Preparamos la inserción directa en la tabla COMPRA
        $stmt = $mysqli->prepare("INSERT INTO COMPRA (USUARIO, COD_PROD, FECHA) VALUES (?, ?, NOW())");
        
        foreach ($produtos as $p) {
            $cod_prod = (int)$p['id'];
            $stmt->bind_param("si", $usuario_id, $cod_prod);
            $stmt->execute();
        }
        $stmt->close();

        // Confirmamos los cambios en la BD
        $mysqli->commit();
        
        // ¡ÉXITO! Vaciamos el carrito
        unset($_SESSION['dados_venda']); 
        unset($_SESSION['carrito']);     
        
        $status_message = 'Suministros adquiridos. El equipo ha sido asignado a tu inventario exitosamente.';
        $status_type = 'success';

    } catch (Exception $e) {
        $mysqli->rollback(); // Revertimos si hay error
        
        // Error 1062 = Ya compraste esta arma antes
        if ($mysqli->errno == 1062) {
            $status_message = "Acceso denegado: Ya posees uno de estos artículos en tu bitácora de equipo.";
        } else {
            $status_message = "Fallo en la asignación: Error en el sistema central.";
        }
        $status_type = 'danger';
    }
    $mysqli->close();
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