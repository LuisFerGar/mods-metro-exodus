<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión.']); exit;
}

$mysqli = new mysqli("localhost", "root", "", "metro_bd", 3306);

$accion = $_POST['accion'] ?? '';
$id_reporte = isset($_POST['id_reporte']) ? (int)$_POST['id_reporte'] : 0;
$usuario_actual = $_SESSION['id_usuario'];

if ($accion === 'eliminar') {
    // ID_REPORTE ahora identifica la fila en la bitácora
    $sql = "DELETE FROM BITACORA WHERE ID_REPORTE = ? AND USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $id_reporte, $usuario_actual);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Comentario eliminado.']);

} elseif ($accion === 'editar') {
    $nuevo_texto = trim($_POST['comentario'] ?? '');
    $sql = "UPDATE BITACORA SET COMENTARIO = ? WHERE ID_REPORTE = ? AND USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sis", $nuevo_texto, $id_reporte, $usuario_actual);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Comentario editado.']);
}
?>