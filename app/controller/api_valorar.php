<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

<?php
// views/pages/api_valorar.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "metro_bd");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
    $puntuacion = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $usuario_id = $_SESSION['id_usuario'] ?? null;

    if ($usuario_id && $producto_id && $puntuacion) {
        $sql = "INSERT INTO valoraciones (usuario_id, producto_id, puntuacion) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE puntuacion = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $producto_id, $puntuacion, $puntuacion);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sesion no detectada']);
    }
}
$mysqli->close();
?>