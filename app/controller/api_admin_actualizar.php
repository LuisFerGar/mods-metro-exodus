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
$categoria = trim($_POST['categoria'] ?? '');
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;
$imagen = trim($_POST['imagen'] ?? '');

if($id <= 0 || $nombre === '' || $categoria === '' || $precio === null || $imagen === ''){
    echo "<script>alert('Datos inválidos'); window.history.back();</script>";
    exit;
}

$stmt = $mysqli->prepare("UPDATE PRODUCTOS 
                          SET NOMBRE_PRODUCTO=?, ID_CATEGORIA=?, PRECIO=?, IMAGEN=? 
                          WHERE COD_PROD=?");
$stmt->bind_param("ssdsi", $nombre, $categoria, $precio, $imagen, $id);

if($stmt->execute()){
    echo "<script>alert('Producto actualizado'); window.location.href='index.php?pagina=admin_dashboard';</script>";
} else {
    echo "<script>alert('Error al actualizar'); window.history.back();</script>";
}