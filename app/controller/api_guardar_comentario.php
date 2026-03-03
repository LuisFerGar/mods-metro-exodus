<?php
// app/controller/api_guardar_comentario.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); 
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Comandante, debes iniciar sesión.']);
    exit;
}

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

$usuario_actual = $_SESSION['id_usuario'];
$id_producto = $_POST['cod_prod'] ?? null;
$comentario = trim($_POST['comentario'] ?? '');

if (!$id_producto || empty($comentario)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos para el reporte.']);
    exit;
}

// Verificar si compró el producto
$sql_verificar = "SELECT ID_COMPRA FROM COMPRA WHERE USUARIO = ? AND COD_PROD = ?";
$stmt_check = $mysqli->prepare($sql_verificar);
$stmt_check->bind_param("si", $usuario_actual, $id_producto);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();

if ($resultado_check->num_rows > 0) {
    // Si lo compró, insertamos el comentario (sin estrellas)
    $sql_insert = "INSERT INTO VALORACIONES (USUARIO, COD_PROD, COMENTARIO) VALUES (?, ?, ?)";
    $stmt_insert = $mysqli->prepare($sql_insert);
    $stmt_insert->bind_param("sis", $usuario_actual, $id_producto, $comentario);
    
    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Reporte guardado en la bitácora.']);
    } else {
        if ($mysqli->errno == 1062) {
            echo json_encode(['status' => 'error', 'message' => 'Ya dejaste un reporte para este artículo. (Usa el botón de editar)']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error interno al guardar.']);
        }
    }
    $stmt_insert->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Debes adquirir este artículo primero.']);
}

$stmt_check->close();
$mysqli->close();
?>