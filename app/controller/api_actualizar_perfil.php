<?php
// app/controller/api_actualizar_perfil.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost"; $basedatos = "metro_bd"; $usuario = "root"; $contrasena = "";
$respuesta = array();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => "Acceso denegado."]); exit;
}

$id_usuario_antiguo = $_SESSION['id_usuario']; // El ID actual (ej: CAR123)

// Recibir datos
$nuevo_usuario = $_POST['usuario_id'] ?? $id_usuario_antiguo; // Nuevo usuario o el mismo
$nuevo_nombre = $_POST['nombre'] ?? '';
$nuevo_correo = $_POST['email'] ?? '';
$nueva_contra = $_POST['new_password'] ?? '';

$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);
if ($mysqli->connect_error) { echo json_encode(['error' => "Error DB"]); exit; }

// VERIFICAR SI EL NUEVO USUARIO YA EXISTE (Si lo cambió)
if ($nuevo_usuario !== $id_usuario_antiguo) {
    $check = $mysqli->prepare("SELECT USUARIO FROM CLIENTE WHERE USUARIO = ?");
    $check->bind_param("s", $nuevo_usuario);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => "El nuevo nombre de usuario ya está ocupado."]);
        exit;
    }
}

// Construir la consulta SQL Dinámica
$tipos = "sss"; // Strings para nombre, correo, nuevo_usuario
$params = [$nuevo_nombre, $nuevo_correo, $nuevo_usuario];
$sql = "UPDATE CLIENTE SET NOMBRE = ?, CORREO = ?, USUARIO = ?";

if (!empty($nueva_contra)) {
    $sql .= ", CONTRA = ?";
    $tipos .= "s";
    $params[] = $nueva_contra;
}

$sql .= " WHERE USUARIO = ?"; // Condición: Buscar por el usuario antiguo
$tipos .= "s";
$params[] = $id_usuario_antiguo;

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($tipos, ...$params);

try {
    if ($stmt->execute()) {
        // ACTUALIZAR SESIÓN CON LOS NUEVOS DATOS
        $_SESSION['nombre_usuario'] = $nuevo_nombre;
        $_SESSION['id_usuario'] = $nuevo_usuario; // ¡Actualizamos la sesión también!
        
        $respuesta['exito'] = "Perfil actualizado correctamente.";
    } else {
        $respuesta['error'] = "Error SQL: " . $stmt->error;
    }
} catch (Exception $e) {
    $respuesta['error'] = "Error crítico (Posible conflicto de compras): " . $e->getMessage();
}

$stmt->close(); $mysqli->close();
echo json_encode($respuesta);
?>