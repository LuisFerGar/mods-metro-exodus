<?php
// app/controller/api_leer_comentarios.php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = ""; 
$port = 3307;

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión.']);
    exit;
}

$id_producto = $_GET['id'] ?? 0;

if (!$id_producto) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no válido.']);
    exit;
}

$sql = "SELECT V.ID_VALORACION, V.USUARIO, V.COMENTARIO, V.FECHA, C.NOMBRE 
        FROM VALORACIONES V 
        JOIN CLIENTE C ON V.USUARIO = C.USUARIO 
        WHERE V.COD_PROD = ? 
        ORDER BY V.FECHA DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();

$comentarios = [];
while ($fila = $resultado->fetch_assoc()) {
    $comentarios[] = $fila;
}

echo json_encode(['status' => 'success', 'data' => $comentarios]);

$stmt->close();
$mysqli->close();
?>