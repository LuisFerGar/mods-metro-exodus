<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión.']); exit;
}

$mysqli = new mysqli("localhost", "root", "", "metro_bd", 3307);

$accion = $_POST['accion'] ?? '';
$id_valoracion = $_POST['id_valoracion'] ?? 0;
$usuario_actual = $_SESSION['id_usuario'];

if ($accion === 'eliminar') {
    $sql = "DELETE FROM VALORACIONES WHERE ID_VALORACION = ? AND USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $id_valoracion, $usuario_actual);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Comentario eliminado.']);

} elseif ($accion === 'editar') {
    $nuevo_texto = trim($_POST['comentario'] ?? '');
    $sql = "UPDATE VALORACIONES SET COMENTARIO = ? WHERE ID_VALORACION = ? AND USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sis", $nuevo_texto, $id_valoracion, $usuario_actual);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Comentario editado.']);
}
?>