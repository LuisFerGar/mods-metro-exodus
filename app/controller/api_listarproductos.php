<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); 
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);
$response = ['productos' => [], 'status' => 'error'];

if ($mysqli->connect_error) {
    $response['message'] = "Error de conexión: " . $mysqli->connect_error;
} else {
    // CORRECCIÓN: Usamos el JOIN porque la tabla CATEGORIAS sí existe en tu BD
    $sql = "SELECT P.COD_PROD, P.NOMBRE_PRODUCTO, P.PRECIO, P.IMAGEN, C.NOMBRE_CATEGORIA AS CATEGORIA 
            FROM PRODUCTOS P 
            LEFT JOIN CATEGORIAS C ON P.ID_CATEGORIA = C.ID_CATEGORIA 
            ORDER BY P.COD_PROD ASC";
            
    $result = $mysqli->query($sql);

    if ($result) {
        $productos = [];
        while ($row = $result->fetch_assoc()) { $productos[] = $row; }
        $response['productos'] = $productos;
        $response['status'] = 'success';
    } else {
        $response['message'] = "Error SQL: " . $mysqli->error;
    }
}
$mysqli->close();
echo json_encode($response);