<?php
// 1. INICIO DE SESIÓN
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$nombre_sesion = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : '';
$usuario_sesion = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : '';

if (!$usuario_sesion) {
    header('Location: index.php?pagina=login');
    exit;
}

// Lógica nombre
if (!empty($nombre_sesion)) {
    $nombre_mostrar = strtoupper($nombre_sesion) . " (" . strtoupper($usuario_sesion) . ")";
} else {
    $nombre_mostrar = strtoupper($usuario_sesion);
}
$usuario_real = strtoupper($usuario_sesion);
$inicial = substr($nombre_mostrar, 0, 1);

// --- CONEXIÓN DIRECTA A BD ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario_db = "root";
$contrasena_db = "";

$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

$historial_compras = [];
$lista_deseos_detalles = [];

if (!$mysqli->connect_error) {
    // A. OBTENER COMPRAS REALIZADAS
    $sql_compras = "SELECT DISTINCT C.ID_COMPRA, C.FECHA, C.COD_PROD, 
                           P.NOMBRE_PRODUCTO, P.PRECIO, P.IMAGEN 
                    FROM COMPRA C 
                    JOIN PRODUCTOS P ON C.COD_PROD = P.COD_PROD 
                    WHERE C.USUARIO = ? 
                    ORDER BY C.FECHA DESC";
    
    $stmt_compras = $mysqli->prepare($sql_compras);
    if ($stmt_compras) {
        $stmt_compras->bind_param("s", $usuario_sesion);
        $stmt_compras->execute();
        $result_compras = $stmt_compras->get_result();
        while ($row = $result_compras->fetch_assoc()) {
            $historial_compras[] = $row;
        }
        $stmt_compras->close();
    }

    // B. OBTENER LISTA DE DESEOS CON DETALLES
    $sql_deseos = "SELECT DISTINCT L.ID_DESEO, L.FECHA_AGREGADO, 
                          P.COD_PROD, P.NOMBRE_PRODUCTO, P.PRECIO, P.IMAGEN 
                   FROM LISTA_DESEOS L 
                   JOIN PRODUCTOS P ON L.COD_PROD = P.COD_PROD 
                   WHERE L.USUARIO = ? 
                   ORDER BY L.FECHA_AGREGADO DESC";
    
    $stmt_deseos = $mysqli->prepare($sql_deseos);
    if ($stmt_deseos) {
        $stmt_deseos->bind_param("s", $usuario_sesion);
        $stmt_deseos->execute();
        $result_deseos = $stmt_deseos->get_result();
        while ($row = $result_deseos->fetch_assoc()) {
            $lista_deseos_detalles[] = $row;
        }
        $stmt_deseos->close();
    }

    $mysqli->close();
}
?>

<div class="container py-5">
    
    <div class="card bg-dark border-secondary shadow-lg mb-5">
        <div class="card-body p-4 d-flex align-items-center flex-wrap">
            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-4 border border-light" 
                 style="width: 80px; height: 80px; font-size: 2.5rem; color: #000; font-family: 'Russo One', sans-serif;">
                <?php echo $inicial; ?>
            </div>
            <div>
                <h2 class="text-light mb-1"><?php echo $nombre_mostrar; ?></h2>
                <p class="text-secondary mb-0 fs-5">Rango: <span class="text-warning">Spartan Ranger</span></p>
                <p class="text-secondary small mb-0">ID Usuario: <?php echo $usuario_real; ?></p>
            </div>
            <div class="ms-auto mt-3 mt-md-0">
                <a href="index.php?pagina=editar_perfil" class="btn btn-outline-secondary"><i class="bi bi-gear-fill me-1"></i> EDITAR DATOS</a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- COMPRAS REALIZADAS -->
        <div class="col-lg-6 mb-4">
            <div class="card bg-black border-secondary h-100 shadow-lg">
                <div class="card-header bg-dark border-secondary p-4">
                    <h4 class="text-warning mb-0"><i class="bi bi-box-seam-fill me-2"></i>Compras Realizadas (<?php echo count($historial_compras); ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($historial_compras)): ?>
                        <div class="text-center py-5">
                            <p class="text-secondary fs-5 mb-2">📦 Aún no has realizado compras.</p>
                            <a href="index.php?pagina=mods" class="btn btn-warning btn-sm">
                                <i class="bi bi-shop me-1"></i>Ir al Catálogo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($historial_compras as $compra): ?>
                            <div class="col-md-6">
                                <div class="card bg-dark border-success border-2 h-100 overflow-hidden">
                                    <!-- Imagen -->
                                    <div style="height: 150px; overflow: hidden; position: relative;">
                                        <img src="<?php echo htmlspecialchars($compra['IMAGEN'] ?? 'img/placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($compra['NOMBRE_PRODUCTO']); ?>"
                                             class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 bg-success text-dark px-2 py-1 m-2" style="border-radius: 5px;">
                                            <small class="fw-bold">✓ Adquirido</small>
                                        </div>
                                    </div>
                                    <!-- Contenido -->
                                    <div class="card-body p-3">
                                        <h6 class="text-warning fw-bold mb-1"><?php echo htmlspecialchars($compra['NOMBRE_PRODUCTO']); ?></h6>
                                        <p class="text-success fw-bold mb-2">Bs. <?php echo number_format($compra['PRECIO'], 2); ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i><?php echo date("d/m/Y", strtotime($compra['FECHA'])); ?>
                                        </small>
                                        <div class="mt-3">
                                            <a href="index.php?pagina=detalle&id=<?php echo $compra['COD_PROD']; ?>" 
                                               class="btn btn-sm btn-outline-warning w-100">
                                                <i class="bi bi-eye me-1"></i>Ver detalles
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- LISTA DE DESEOS -->
        <div class="col-lg-6 mb-4">
            <div class="card bg-black border-secondary h-100 shadow-lg">
                <div class="card-header bg-dark border-secondary p-4">
                    <h4 class="text-warning mb-0"><i class="bi bi-heart-fill me-2"></i>Lista de Deseos (<?php echo count($lista_deseos_detalles); ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($lista_deseos_detalles)): ?>
                        <div class="text-center py-5">
                            <p class="text-secondary fs-5 mb-2">💭 Tu lista de deseos está vacía.</p>
                            <small class="text-secondary d-block mb-3">Agrega artículos desde el catálogo haciendo clic en el corazón.</small>
                            <a href="index.php?pagina=mods" class="btn btn-warning btn-sm">
                                <i class="bi bi-search me-1"></i>Explorar Catálogo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($lista_deseos_detalles as $item): ?>
                            <div class="col-md-6">
                                <div class="card bg-dark border-danger border-2 h-100 overflow-hidden">
                                    <!-- Imagen -->
                                    <div style="height: 150px; overflow: hidden; position: relative;">
                                        <img src="<?php echo htmlspecialchars($item['IMAGEN'] ?? 'img/placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['NOMBRE_PRODUCTO']); ?>"
                                             class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 m-2" style="border-radius: 5px;">
                                            <i class="bi bi-heart-fill"></i>
                                        </div>
                                    </div>
                                    <!-- Contenido -->
                                    <div class="card-body p-3">
                                        <h6 class="text-warning fw-bold mb-1"><?php echo htmlspecialchars($item['NOMBRE_PRODUCTO']); ?></h6>
                                        <p class="text-warning fw-bold mb-2">Bs. <?php echo number_format($item['PRECIO'], 2); ?></p>
                                        <div class="d-flex gap-2">
                                            <a href="index.php?pagina=detalle&id=<?php echo $item['COD_PROD']; ?>" 
                                               class="btn btn-sm btn-outline-warning flex-grow-1">
                                                <i class="bi bi-search me-1"></i>Ver
                                            </a>
                                            <button onclick="eliminarDeseoPerfil(<?php echo $item['COD_PROD']; ?>)" 
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarDeseoPerfil(id_producto) {
    if (!confirm('¿Eliminar de tu lista de deseos?')) return;
    
    fetch('/index.php?pagina=api_wishlist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'producto_id=' + id_producto + '&accion=eliminar'
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'removed') {
            alert('✓ Eliminado de tu lista de deseos');
            location.reload();
        } else if (data.status === 'error') {
            alert('Error: ' + (data.message || 'No se pudo eliminar'));
        } else {
            location.reload();
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error de conexión');
    });
}
</script>