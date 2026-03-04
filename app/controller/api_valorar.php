<?php
// app/controller/api_valorar.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- EL BORRADOR MÁGICO ---
// Destruye el HTML del header para que solo salga texto puro JSON
ob_clean();
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "metro_bd");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
    $puntuacion = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    
    // Sincronizado con tus variables de sesión reales
    $usuario_id = $_SESSION['id_usuario'] ?? $_SESSION['usuario_logueado'] ?? null;

    if ($usuario_id && $producto_id && $puntuacion) {
        $sql = "INSERT INTO valoraciones (usuario_id, producto_id, puntuacion) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE puntuacion = ?";
        $stmt = $mysqli->prepare($sql);
        
        // "s" = string (usuario), "i" = enteros (resto)
        $stmt->bind_param("siii", $usuario_id, $producto_id, $puntuacion, $puntuacion);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $mysqli->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos o sesión cerrada.']);
    }
}
$mysqli->close();
?>