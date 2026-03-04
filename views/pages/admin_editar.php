<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['usuario_logueado']) || ($_SESSION['rol_usuario'] ?? '') !== 'admin') {
    echo "<script>alert('ACCESO DENEGADO'); window.location.href='index.php';</script>";
    exit;
}

$hostname="localhost";
$basedatos="metro_bd";
$usuario="root";
$contrasena="";

$mysqli = new mysqli($hostname,$usuario,$contrasena,$basedatos);
if($mysqli->connect_error){ die("Error DB: ".$mysqli->connect_error); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0){
    echo "<div class='alert alert-danger'>ID inválido</div>";
    exit;
}

// 1. BUSCAR EL PRODUCTO
$stmt = $mysqli->prepare("SELECT * FROM PRODUCTOS WHERE COD_PROD=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();

if(!$prod){
    echo "<div class='alert alert-danger'>Producto no encontrado</div>";
    exit;
}

// 2. BUSCAR TODAS LAS CATEGORÍAS (De la tabla real)
$categorias = [];
$res_cat = $mysqli->query("SELECT ID_CATEGORIA, NOMBRE_CATEGORIA FROM CATEGORIAS");
if($res_cat) {
    while($row = $res_cat->fetch_assoc()) {
        $categorias[] = $row;
    }
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-warning fw-bold mb-0">EDITAR SUMINISTRO #<?php echo (int)$prod['COD_PROD']; ?></h3>
        <a href="index.php?pagina=admin_dashboard" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card bg-dark border-secondary text-light">
        <div class="card-body p-4">
            <form method="POST" action="index.php?pagina=api_admin_actualizar">
                <input type="hidden" name="id" value="<?php echo (int)$prod['COD_PROD']; ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input class="form-control bg-secondary text-light border-0"
                           name="nombre" required
                           value="<?php echo htmlspecialchars($prod['NOMBRE_PRODUCTO']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select class="form-select bg-secondary text-light border-0" name="categoria" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['ID_CATEGORIA']; ?>" <?php echo ($cat['ID_CATEGORIA'] == $prod['ID_CATEGORIA']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['NOMBRE_CATEGORIA']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio (Bs)</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control bg-secondary text-light border-0"
                           name="precio" required
                           value="<?php echo htmlspecialchars($prod['PRECIO']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen (URL o ruta)</label>
                    <input class="form-control bg-secondary text-light border-0"
                           name="imagen" required
                           value="<?php echo htmlspecialchars($prod['IMAGEN']); ?>">
                    <small class="text-secondary">Ej: img/mods/arma1.jpg</small>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-warning fw-bold">
                        <i class="bi bi-save2-fill me-2"></i>Guardar cambios
                    </button>
                    <a class="btn btn-outline-danger"
                       href="index.php?pagina=admin_dashboard">
                       Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>