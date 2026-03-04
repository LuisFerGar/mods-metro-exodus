<?php
// Asegurar que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// CRITICAL: Enviar header JSON ANTES de cualquier output
header('Content-Type: application/json; charset=utf-8');

// Obtener datos de la sesión y POST
$usuario = $_SESSION['id_usuario'] ?? null;
$id_prod = isset($_POST['cod_prod']) ? (int)$_POST['cod_prod'] : null;
$comentario = trim($_POST['comentario'] ?? '');

// Validar datos básicos
if (!$usuario || !$id_prod || $comentario === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos: usuario, producto o comentario faltante']);
    exit;
}

// CONEXIÓN A LA BD
$mysqli = new mysqli("localhost", "root", "", "metro_bd");

// Verificar conexión
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Conexión BD fallida: ' . $mysqli->connect_error]);
    exit;
}

// PASO 1: Verificar que el usuario compró el producto
$stmt_check = $mysqli->prepare("SELECT ID_COMPRA FROM COMPRA WHERE USUARIO = ? AND COD_PROD = ?");
if (!$stmt_check) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error preparando query de verificación']);
    $mysqli->close();
    exit;
}

$stmt_check->bind_param("si", $usuario, $id_prod);
if (!$stmt_check->execute()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error ejecutando verificación']);
    $stmt_check->close();
    $mysqli->close();
    exit;
}

$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No compraste este producto, no puedes reportarlo']);
    $stmt_check->close();
    $mysqli->close();
    exit;
}
$stmt_check->close();

// PASO 2: Intentar insertar el comentario
$stmt = $mysqli->prepare("INSERT INTO BITACORA (USUARIO, COD_PROD, COMENTARIO) VALUES (?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error preparando insert']);
    $mysqli->close();
    exit;
}

$stmt->bind_param("sis", $usuario, $id_prod, $comentario);
$resultado = $stmt->execute();

if ($resultado) {
    // ÉXITO
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Reporte guardado exitosamente']);
} else {
    // ERROR EN INSERT
    if ($mysqli->errno === 1062) {
        // Duplicate entry = ya reportó este producto
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Ya dejaste un reporte para este artículo']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $mysqli->error]);
    }
}

$stmt->close();
$mysqli->close();
exit;
