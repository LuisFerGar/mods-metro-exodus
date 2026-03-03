<?php
// app/controller/api_registrarse.php

// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiamos la salida para garantizar que solo salga JSON
ob_clean(); 
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$basedatos = "metro_bd"; 
$usuario = "root";
$contrasena = "";
$port = 3307;

$respuesta = array();

// 2. Validar que llegaron los datos
if (!isset($_POST['nombre'], $_POST['correo'], $_POST['usuario'], $_POST['password'])) {
    echo json_encode(['error' => "Faltan datos obligatorios."]);
    exit;
}

$reg_nombre = $_POST['nombre'];
$reg_correo = $_POST['correo'];
$reg_usuario = $_POST['usuario'];
$reg_contra = $_POST['password']; // Texto plano

$msqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($msqli->connect_error) {
    echo json_encode(['error' => "ERROR DE CONEXIÓN: " . $msqli->connect_error]);
    exit;
}

// 3. Insertar en la Base de Datos
$sql_registro = "INSERT INTO CLIENTE (NOMBRE, CORREO, USUARIO, CONTRA) VALUES (?, ?, ?, ?)";
$stmt = $msqli->prepare($sql_registro);

if ($stmt) {
    try {
        $stmt->bind_param("ssss", $reg_nombre, $reg_correo, $reg_usuario, $reg_contra);

        if ($stmt->execute()) {
            
            // --- CORRECCIÓN IMPORTANTE AQUÍ ---
            // Antes decía 'usuario_logado'. Ahora lo ponemos en ESPAÑOL para que coincida con tu Header.
            $_SESSION['usuario_logueado'] = true;
            
            // Guardamos los datos del usuario para que el perfil funcione de inmediato
            $_SESSION['id_usuario'] = $reg_usuario;
            $_SESSION['nombre_usuario'] = $reg_nombre;
            
            $respuesta['exito'] = "¡Recluta registrado con éxito! Bienvenido al Aurora.";

        } 
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $respuesta['error'] = "Fallo: El apodo '$reg_usuario' o el correo ya están registrados.";
        } else {
            $respuesta['error'] = "Error de Base de Datos: " . $e->getMessage();
        }
    }
    $stmt->close();
} else {
    $respuesta['error'] = "Fallo SQL: " . $msqli->error;
}

$msqli->close();

// Enviar respuesta
echo json_encode($respuesta);
?>