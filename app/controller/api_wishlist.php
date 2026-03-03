<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$basedatos = "metro_bd";
$usuario = "root";
$contrasena = "";
$port = 3306;

$respuesta = array();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => "Debes iniciar sesión para usar la lista de deseos."]);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

if (!isset($_POST['producto_id'])) {
    echo json_encode(['status' => 'error', 'message' => "Falta el ID del producto."]);
    exit;
}
$id_producto = (int)$_POST['producto_id'];

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "Error DB: " . $mysqli->connect_error]);
    exit;
}

$check_sql = "SELECT ID_DESEO FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?";
$stmt_check = $mysqli->prepare($check_sql);
$stmt_check->bind_param("si", $id_usuario, $id_producto);
$stmt_check->execute();
$resultado = $stmt_check->get_result();

if ($resultado->num_rows > 0) {
    $delete_sql = "DELETE FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?";
    $stmt_del = $mysqli->prepare($delete_sql);
    $stmt_del->bind_param("si", $id_usuario, $id_producto);
    
    if ($stmt_del->execute()) {
        $respuesta['status'] = 'removed';
        $respuesta['message'] = 'Eliminado de la lista de deseos.';
        $respuesta['icon'] = 'bi-heart';
    } else {
        $respuesta['status'] = 'error';
        $respuesta['message'] = 'Error al eliminar.';
    }
    $stmt_del->close();

} else {
    $insert_sql = "INSERT INTO LISTA_DESEOS (USUARIO, COD_PROD) VALUES (?, ?)";
    $stmt_add = $mysqli->prepare($insert_sql);
    $stmt_add->bind_param("si", $id_usuario, $id_producto);
    
    if ($stmt_add->execute()) {
        $respuesta['status'] = 'added';
        $respuesta['message'] = 'Agregado a la lista de deseos.';
        $respuesta['icon'] = 'bi-heart-fill';
    } else {
        $respuesta['status'] = 'error';
        $respuesta['message'] = 'Error al guardar.';
    }
    $stmt_add->close();
}

$stmt_check->close();
$mysqli->close();

echo json_encode($respuesta);
?>