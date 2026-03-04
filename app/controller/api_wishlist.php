<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

if (!isset($_POST['producto_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parámetro producto_id faltante']);
    exit;
}

$id_usuario = trim($_SESSION['id_usuario']);
$id_producto = (int) trim($_POST['producto_id']);
$accion = isset($_POST['accion']) ? trim($_POST['accion']) : 'toggle';

if ($id_producto < 1) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$mysqli = @new mysqli('localhost', 'root', '', 'metro_bd', 3306);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de BD']);
    exit;
}

$mysqli->set_charset('utf8mb4');

// === ELIMINAR ===
if ($accion === 'eliminar') {
    $stmt = $mysqli->prepare('DELETE FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error BD']);
        exit;
    }
    
    $stmt->bind_param('si', $id_usuario, $id_producto);
    $stmt->execute();
    
    http_response_code(200);
    echo json_encode(['status' => 'removed', 'message' => 'Eliminado']);
    $stmt->close();
    $mysqli->close();
    exit;
}

// === AGREGAR ===
if ($accion === 'agregar' || $accion === 'toggle') {
    // Verificar que existe el producto
    $stmt = $mysqli->prepare('SELECT 1 FROM PRODUCTOS WHERE COD_PROD = ?');
    $stmt->bind_param('i', $id_producto);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    if (!$exists) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Producto no existe']);
        $mysqli->close();
        exit;
    }
    
    // Verificar si ya está
    $stmt = $mysqli->prepare('SELECT 1 FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?');
    $stmt->bind_param('si', $id_usuario, $id_producto);
    $stmt->execute();
    $ya_existe = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    
    // Si es toggle y existe, eliminar
    if ($accion === 'toggle' && $ya_existe) {
        $stmt = $mysqli->prepare('DELETE FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?');
        $stmt->bind_param('si', $id_usuario, $id_producto);
        $stmt->execute();
        http_response_code(200);
        echo json_encode(['status' => 'removed', 'message' => 'Eliminado']);
        $stmt->close();
    } else if (!$ya_existe) {
        // Agregar
        $stmt = $mysqli->prepare('INSERT INTO LISTA_DESEOS (USUARIO, COD_PROD) VALUES (?, ?)');
        $stmt->bind_param('si', $id_usuario, $id_producto);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['status' => 'added', 'message' => 'Agregado']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al agregar']);
        }
        $stmt->close();
    } else {
        http_response_code(200);
        echo json_encode(['status' => 'already_added', 'message' => 'Ya en lista']);
    }
}

$mysqli->close();
?>