<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "metro_bd");

$id_prod = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$response = ['status' => 'error', 'data' => []];

if ($id_prod > 0) {
    // Usamos la tabla BITACORA y extraemos el nombre del usuario desde CLIENTE
    $sql = "SELECT B.ID_REPORTE, B.COMENTARIO, B.FECHA, B.USUARIO, C.NOMBRE 
            FROM BITACORA B 
            INNER JOIN CLIENTE C ON B.USUARIO = C.USUARIO 
            WHERE B.COD_PROD = ? 
            ORDER BY B.FECHA DESC";
            
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_prod);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comentarios = [];
    while ($row = $result->fetch_assoc()) {
        $comentarios[] = $row;
    }
    $response = ['status' => 'success', 'data' => $comentarios];
}

echo json_encode($response);
$mysqli->close();