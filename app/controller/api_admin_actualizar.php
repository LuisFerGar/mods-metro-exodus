<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header("Content-Type: text/html; charset=utf-8");

if(!isset($_SESSION['usuario_logueado']) || ($_SESSION['rol_usuario'] ?? '') !== 'admin') {
    echo "<script>alert('ACCESO DENEGADO'); window.location.href='index.php';</script>";
    exit;
}

$hostname="localhost";
$basedatos="metro_bd";
$usuario="root";
$contrasena="";
$puerto=3306;

$mysqli = new mysqli($hostname,$usuario,$contrasena,$basedatos, $puerto);
if($mysqli->connect_error){ die("Error DB: ".$mysqli->connect_error); }

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$id_categoria = isset($_POST['categoria']) ? (int)$_POST['categoria'] : 0;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;
$imagen = trim($_POST['imagen'] ?? '');

if($id <= 0 || $nombre === '' || $id_categoria <= 0 || $precio === null || $imagen === ''){
    echo "<script>alert('Datos inválidos'); window.history.back();</script>";
    exit;
}

$sql = "UPDATE PRODUCTOS SET NOMBRE_PRODUCTO = ?, ID_CATEGORIA = ?, PRECIO = ?, IMAGEN = ? WHERE COD_PROD = ?";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo "<script>alert('Error en la preparación de la consulta'); window.history.back();</script>";
    exit;
}

// Tipos: s = string (nombre), i = integer (id_categoria), d = double (precio), s = string (imagen), i = integer (id)
$stmt->bind_param("sidsi", $nombre, $id_categoria, $precio, $imagen, $id);

if($stmt->execute()){
    // Usamos una flash message en sesión para que el dashboard muestre la confirmación
    $_SESSION['flash_success'] = 'Producto actualizado correctamente.';
    header('Location: index.php?pagina=admin_dashboard');
    exit;
} else {
    $_SESSION['flash_error'] = 'Error al actualizar: ' . $stmt->error;
    header('Location: index.php?pagina=admin_editar&id=' . $id);
    exit;
}