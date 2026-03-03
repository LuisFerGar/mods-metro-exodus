<?php
// app/controller/api_admin_eliminar.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json');

// 1. Seguridad: Solo Admin
if (!isset($_SESSION['rol_usuario']) || $_SESSION['rol_usuario'] !== 'admin') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado.']);
    exit;
}

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";
$port = 3307;
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);


if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error DB: ' . $mysqli->connect_error]); 
    exit;
}

// 2. Recibir ID
$id_producto = $_POST['id'] ?? null;

// --- CORRECCIÓN 1: Lógica Inversa ---
// Agregamos el signo '!' (NOT). 
// "Si NO hay ID, entonces lanza el error".
if (!$id_producto) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de producto no proporcionado.']);
    exit;
}

// 3. Borrar
$sql = "DELETE FROM PRODUCTOS WHERE COD_PROD = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_producto);

if ($stmt->execute()) {
    // --- CORRECCIÓN 2: Estandarizar respuesta ---
    // Usamos 'status' y 'mensaje' en todas partes
    echo json_encode(['status' => 'success', 'mensaje' => 'Suministro eliminado correctamente.']);
} else {
    // Error 1451: Integridad referencial (Ya comprado)
    if ($mysqli->errno == 1451) {
        echo json_encode([
            'status' => 'error', 
            'mensaje' => 'PROTECCIÓN ACTIVA: No se puede borrar este producto porque ya fue comprado por un usuario.'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error SQL: ' . $stmt->error]);
    }
}

$stmt->close();
$mysqli->close();
?>