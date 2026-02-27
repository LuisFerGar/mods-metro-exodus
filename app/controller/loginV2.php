<?php
// app/controller/loginV2.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hostname = "localhost";
$basedatos = "metro_bd"; 
$usuario = "root";
$contrasena = "";

$loginusuario = $_POST['usuario'];
$logincontra = $_POST['password'];

$msqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

if ($msqli->connect_error) {
    die("Error DB: " . $msqli->connect_error);
}

// 1. CAMBIO AQUÍ: Agregamos ', ROL' a la consulta
$stmt = $msqli->prepare("SELECT USUARIO, NOMBRE, CONTRA, ROL FROM CLIENTE WHERE USUARIO = ?");
$stmt->bind_param("s", $loginusuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    
    if ($logincontra === $row['CONTRA']) {
        
        // 2. CAMBIO AQUÍ: Guardamos el ROL en la sesión
        $_SESSION['usuario_logueado'] = true;
        $_SESSION['id_usuario'] = $row['USUARIO'];
        $_SESSION['nombre_usuario'] = $row['NOMBRE'];
        $_SESSION['rol_usuario'] = $row['ROL']; // <--- ESTA ES LA CLAVE QUE FALTABA
        
        echo "<script>window.location.href='index.php?pagina=inicio';</script>";
        exit();
        
    } else {
        echo "<script>alert('Contraseña incorrecta.'); window.location.href='index.php?pagina=login';</script>";
    }
} else {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='index.php?pagina=login';</script>";
}

$stmt->close();
$msqli->close();
?>