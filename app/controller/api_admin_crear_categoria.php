<?php
// app/controller/api_admin_crear_categoria.php
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

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error de conexión a la BD.']);
    exit;
}

// 2. Recibir datos
$nombre_cat = trim($_POST['nombre_categoria'] ?? '');

if (empty($nombre_cat)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'El nombre de la categoría no puede estar vacío.']);
    exit;
}

// 3. Insertar en la BD (Asumiendo que tu tabla es CATEGORIAS y la columna NOMBRE_CATEGORIA)
$sql = "INSERT INTO CATEGORIAS (NOMBRE_CATEGORIA) VALUES (?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $nombre_cat);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'mensaje' => 'Categoría agregada correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error SQL: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>