<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json');

if(!isset($_SESSION['usuario_logueado']) || $_SESSION['rol_usuario'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit();
}

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";
$port = 3307;
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);


if($mysqli->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$nombre=$_POST['nombre'];
$precio=$_POST['precio'];
$categ=$_POST['categoria'];
$desc=$_POST['descripcion'];

$ruta_final_bd='img/placeholder.jpg';

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = $_FILES['imagen']['name'];
    $temp_path = $_FILES['imagen']['tmp_name'];
    $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
    $nuevo_nombre = "mod_".uniqid() . '.' . $extension;
    $carpeta_destino = '../public/img/mods/';
    if(!is_dir($carpeta_destino)) { mkdir($carpeta_destino, 0777, true); }
    $destino_final = $carpeta_destino . $nuevo_nombre;
    if(move_uploaded_file($temp_path, $destino_final)) {
        $ruta_final_bd = 'img/mods/' . $nuevo_nombre;
    }else{
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
        exit();
    }
}

$sql="INSERT INTO PRODUCTOS (NOMBRE_PRODUCTO, PRECIO, CATEGORIA, DESCRIPCION, IMAGEN) VALUES (?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sdsss", $nombre, $precio, $categ, $desc, $ruta_final_bd);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Producto creado exitosamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al crear el producto.'.$stmt->error]);
}

$stmt->close();
$mysqli->close();

?>