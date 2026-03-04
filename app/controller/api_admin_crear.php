<?php
// Conexión para traer categorías
$mysqli_cat = new mysqli("localhost", "root", "", "metro_bd");
$lista_categorias = [];
if (!$mysqli_cat->connect_error) {
    $resultado = $mysqli_cat->query("SELECT * FROM CATEGORIAS");
    while ($fila = $resultado->fetch_assoc()) {
        $lista_categorias[] = $fila;
    }
}

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
$port = 3306;
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if($mysqli->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$nombre = $_POST['nombre'];
$precio = $_POST['precio'];
$categ_input = $_POST['categoria'];
$desc = $_POST['descripcion'];

$ruta_final_bd = 'img/placeholder.jpg';

// Lógica de subida de imagen
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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
        exit();
    }
}

// --- MAGIA: Traductor de Categoría a ID_CATEGORIA ---
$id_categoria = 1; // Valor por defecto por si acaso

if (is_numeric($categ_input)) {
    // Si el formulario ya manda el número, lo usamos directo
    $id_categoria = (int)$categ_input;
} else {
    // Si manda texto (ej: "Suministros"), buscamos su ID en tu tabla CATEGORIAS
    $stmt_cat = $mysqli->prepare("SELECT ID_CATEGORIA FROM CATEGORIAS WHERE NOMBRE_CATEGORIA = ?");
    if ($stmt_cat) {
        $stmt_cat->bind_param("s", $categ_input);
        $stmt_cat->execute();
        $res_cat = $stmt_cat->get_result();
        if ($row_cat = $res_cat->fetch_assoc()) {
            $id_categoria = $row_cat['ID_CATEGORIA'];
        }
        $stmt_cat->close();
    }
}

// --- CORRECCIÓN: Usar ID_CATEGORIA en el INSERT ---
$sql = "INSERT INTO PRODUCTOS (NOMBRE_PRODUCTO, PRECIO, ID_CATEGORIA, DESCRIPCION, IMAGEN) VALUES (?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);

// "s" = string (nombre), "d" = double/decimal (precio), "i" = integer (id_categoria), "s" = string (descripcion), "s" = string (imagen)
$stmt->bind_param("sdiss", $nombre, $precio, $id_categoria, $desc, $ruta_final_bd);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Producto creado exitosamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al crear el producto. ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>