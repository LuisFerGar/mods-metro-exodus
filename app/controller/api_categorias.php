<?php
// app/controller/api_categorias.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); 
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";

$response = array('status' => 'error', 'categorias' => []);

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

if (!$mysqli->connect_error) {
    $sql = "SELECT ID_CATEGORIA, NOMBRE_CATEGORIA FROM CATEGORIAS ORDER BY NOMBRE_CATEGORIA ASC"; 
    $resultado = $mysqli->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $lista = [];
        while ($row = $resultado->fetch_assoc()) {
            $lista[] = $row;
        }
        $response['categorias'] = $lista;
        $response['status'] = 'success';
    } else {
        $response['status'] = 'success'; // Está vacía
    }
} else {
    $response['message'] = "Error de conexión BD";
}

$mysqli->close();
echo json_encode($response);
?>