<?php
header('Content-Type: application/json');

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

if($mysqli->connect_error) {
    echo json_encode(['status'=> 'error']);
    exit;
}

$usuario_id = $_GET['usuario'] ?? null;

if($usuario_id) {
    $sql = "SELECT COD_PROD FROM LISTA_DESEOS WHERE USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $usuario_id);
    $stmt->execute();
    $resultado=$stmt->get_result();

    $ids =[];
    while($fila = $resultado->fetch_assoc()) {
        $ids[]=$fila['COD_PROD'];
    }
    echo json_encode(['status'=>'success', 'data'=> $ids]);
}else {
    echo json_encode(['status'=>'error', 'message'=>'Falta usuario']);
}
$mysqli->close();

?>