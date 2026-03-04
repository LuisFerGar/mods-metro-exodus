<?php
// app/controller/api_admin_eliminar.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json');

// 1. Seguridad: Solo Admin
if (!isset($_SESSION['rol_usuario']) || $_SESSION['rol_usuario'] !== 'admin') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado.']);
    exit;
}

// Configurar PHP para que trate los errores de MySQL como Excepciones atrapables
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $hostname = "localhost"; 
    $basedatos = "metro_bd"; 
    $usuario = "root"; 
    $contrasena = "";
    $port = 3306;
    $mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

    // 2. Recibir ID
    $id_producto = $_POST['id'] ?? null;

    if (!$id_producto) {
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de producto no proporcionado.']);
        exit;
    }

    // 3. Borrar
    $sql = "DELETE FROM PRODUCTOS WHERE COD_PROD = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_producto);

    // Intentamos ejecutar. Si falla por la llave foránea, saltará directo al bloque "catch"
    $stmt->execute();
    
    echo json_encode(['status' => 'success', 'mensaje' => 'Suministro eliminado correctamente.']);
    
    $stmt->close();
    $mysqli->close();

} catch (mysqli_sql_exception $e) {
    // Si la BD lanza una advertencia, caemos aquí suavemente
    
    // Error 1451: Integridad referencial (Ya comentado, comprado o valorado)
    if ($e->getCode() == 1451) {
        echo json_encode([
            'status' => 'error', 
            'mensaje' => 'PROTECCIÓN ACTIVA: No se puede borrar este producto porque ya tiene valoraciones, reportes o ha sido comprado.'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error SQL: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    // Por si ocurre cualquier otro error en PHP
    echo json_encode(['status' => 'error', 'mensaje' => 'Error de servidor: ' . $e->getMessage()]);
}
?>