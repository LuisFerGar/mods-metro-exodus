<?php
header('Content-Type: application/json');

// 1. CONEXIÓN
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario = "root";
$contrasena = "";
$port = 3306;
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión DB']);
    exit();
}

// 2. OBTENER USUARIO
$usuario_id = $_GET['usuario'] ?? null;

if ($usuario_id) {
    // Consulta SIMPLE para probar (sin JOIN complejo primero)
    // Asegúrate de que las columnas COD_PROD existen en ambas tablas
    $sql = "SELECT P.COD_PROD, P.NOMBRE_PRODUCTO, C.FECHA
            FROM COMPRA C
            JOIN PRODUCTOS P ON C.COD_PROD = P.COD_PROD
            WHERE C.USUARIO = ?
            ORDER BY C.FECHA DESC";

    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        // Si la consulta falla, devuelve el error SQL para que sepamos qué pasa
        echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $mysqli->error]);
        exit();
    }

    $stmt->bind_param("s", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $compras = [];
    while ($fila = $resultado->fetch_assoc()) {
        $compras[] = $fila;
    }

    echo json_encode(['status' => 'success', 'data' => $compras]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Falta el parámetro usuario']);
}

$mysqli->close();
?>