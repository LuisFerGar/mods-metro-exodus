<?php
// app/controller/api_contacto.php

// 1. Iniciar sesión si hace falta (para evitar errores de sesión)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Definir el header JSON
header('Content-Type: application/json; charset=utf-8');

// 3. Conexión
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario = "root";
$contrasena = "";
$port = 3306;
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

// Variable para guardar la respuesta final
$respuesta = [];

if ($mysqli->connect_error) {
    $respuesta = ['status' => 'error', 'message' => "Error de conexión: " . $mysqli->connect_error];
    echo json_encode($respuesta);
    exit;
}

// 4. Recibir datos
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (empty($nombre) || empty($email) || empty($mensaje)) {
    $respuesta = ['status' => 'error', 'message' => "Por favor, completa todos los campos obligatorios."];
    echo json_encode($respuesta);
    exit;
}

// 5. Guardar en BD
$sql = "INSERT INTO MENSAJES (NOMBRE_REMITENTE, EMAIL_REMITENTE, TIPO_SOLICITUD, MENSAJE) VALUES (?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nombre, $email, $asunto, $mensaje);

    if ($stmt->execute()) {
        // ÉXITO: Guardamos el mensaje en la variable
        $respuesta = [
            'status' => 'success',
            'message' => "Transmisión recibida. El equipo del Aurora te contactará pronto."
        ];
    } else {
        $respuesta = ['status' => 'error', 'message' => "Error al guardar: " . $stmt->error];
    }
    $stmt->close();
} else {-
    $respuesta = ['status' => 'error', 'message' => "Error en la consulta SQL: " . $mysqli->error];
}

$mysqli->close();

// 6. LIMPIEZA Y ENVÍO FINAL
// Borramos cualquier salida anterior (como espacios en blanco o echos previos)
ob_clean(); 
// Enviamos la respuesta JSON definitiva
echo json_encode($respuesta);
?>