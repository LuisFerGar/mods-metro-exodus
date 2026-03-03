<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); // Limpieza vital
header('Content-Type: application/json; charset=utf-8');

// --- Configuraciones de la Base de Datos ---
$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";
$port = 3306;

// Array para almacenar la respuesta de la API
$response = array();
$response['productos'] = [];
$response['status'] = 'error'; // Estado inicial de error

// Conexión con la Base de Datos
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    // ERROR DE CONEXIÓN
    $response['message'] = "¡ERROR DE CONEXIÓN CON LA BASE DE DATOS! Detalles: " . $mysqli->connect_error;
} else {
    // AQUÍ ESTÁ LA MAGIA: Usamos INNER JOIN para juntar el producto con el nombre de su categoría
    $sql = "SELECT P.*, C.NOMBRE_CATEGORIA as CATEGORIA 
            FROM PRODUCTOS P 
            INNER JOIN CATEGORIAS C ON P.ID_CATEGORIA = C.ID_CATEGORIA";
    
    // Ejecuta la consulta
    $result_productos = $mysqli->query($sql);

    if ($result_productos) {
        if ($result_productos->num_rows > 0) {
            // Si hay productos, iteramos sobre los resultados
            $productos = [];
            while ($row = $result_productos->fetch_assoc()) {
                $productos[] = $row;
            }
            
            // Definimos el array de productos y el estado de éxito
            $response['productos'] = $productos;
            $response['status'] = 'success';
            $response['message'] = 'Productos recuperados con éxito.';
            
            $result_productos->free();
        } else {
            // Si la tabla está vacía
            $response['message'] = 'Ningún producto encontrado en la tabla PRODUCTOS.';
            $response['status'] = 'success';
        }
    } else {
        // ERROR EN LA QUERY
        $response['message'] = '¡ERROR SQL! Detalles: ' . $mysqli->error;
    }
}

// Cierra la conexión con la base de datos
$mysqli->close();

// Retorna la respuesta en formato JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>