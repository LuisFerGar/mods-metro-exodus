<?php
// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Lógica de agregar al carrito
if (isset($_GET['add_prod']) && is_numeric($_GET['add_prod'])) {
    $producto_id = (int)$_GET['add_prod'];
    if (isset($_SESSION['carrito'][$producto_id])) {
        $_SESSION['carrito'][$producto_id]++;
    } else {
        $_SESSION['carrito'][$producto_id] = 1;
    }
    header('Location: index.php?pagina=mods');
    exit(); 
}

// --- CONEXIÓN DIRECTA Y SEGURA A LA BASE DE DATOS ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario_db = "root";
$contrasena_db = "";

$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

if ($mysqli->connect_error) {
    die("<div class='alert alert-danger text-center mt-5'>Error fatal de conexión al servidor del Metro.</div>");
}

// --- OBTENER COMPRAS Y DESEOS DEL USUARIO ---
$ids_comprados = []; 
$ids_deseados = [];
$usuario_id = $_SESSION['id_usuario'] ?? null;

if($usuario_id) {
    // Buscar Compras (Directo)
    $stmt_c = $mysqli->prepare("SELECT COD_PROD FROM COMPRA WHERE USUARIO = ?");
    if($stmt_c) {
        $stmt_c->bind_param("s", $usuario_id);
        $stmt_c->execute();
        $res_c = $stmt_c->get_result();
        while($row = $res_c->fetch_assoc()) { $ids_comprados[] = (int)$row['COD_PROD']; }
        $stmt_c->close();
    }

    // Buscar Deseos (Directo)
    $stmt_d = $mysqli->prepare("SELECT COD_PROD FROM LISTA_DESEOS WHERE USUARIO = ?");
    if ($stmt_d) {
        $stmt_d->bind_param("s", $usuario_id);
        $stmt_d->execute();
        $res_d = $stmt_d->get_result();
        while($row = $res_d->fetch_assoc()) { $ids_deseados[] = (int)$row['COD_PROD']; }
        $stmt_d->close();
    }
}

// --- CARGAR CATÁLOGO (Directo de PRODUCTOS con JOIN a CATEGORIAS) ---
$productos = [];
$mensagem_catalogo = '';

// Leemos categoría solicitada (si existe) y la escapamos
$categoria_seleccionada = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$safe_cat = $mysqli->real_escape_string($categoria_seleccionada);

$sql = "SELECT P.COD_PROD, P.NOMBRE_PRODUCTO, P.PRECIO, P.IMAGEN, C.NOMBRE_CATEGORIA AS CATEGORIA \n".
       "FROM PRODUCTOS P LEFT JOIN CATEGORIAS C ON P.ID_CATEGORIA = C.ID_CATEGORIA ";

if ($safe_cat !== '') {
    $sql .= "WHERE C.NOMBRE_CATEGORIA = '" . $safe_cat . "' ";
}

$sql .= "ORDER BY P.COD_PROD ASC";

$res_p = $mysqli->query($sql);

if ($res_p) {
    while($row = $res_p->fetch_assoc()) {
        $productos[] = $row;
    }
    if (empty($productos)) {
        $mensagem_catalogo = 'No hay suministros en este momento.';
    } else {
        $mensagem_catalogo = 'Suministros disponibles: ' . count($productos);
        if ($safe_cat !== '') {
            $mensagem_catalogo = 'Categoría: ' . htmlspecialchars($categoria_seleccionada) . ' (' . count($productos) . ')';
        }
    }
} else {
    $mensagem_catalogo = 'Error al consultar la base de datos de suministros.';
}

$mysqli->close(); // Cerramos conexión local

// --- FILTRO POR CATEGORÍA --- (aplicado en la consulta SQL)

// --- FILTRO POR PRECIO ---
$min = filter_input(INPUT_GET, 'min', FILTER_VALIDATE_FLOAT);
$max = filter_input(INPUT_GET, 'max', FILTER_VALIDATE_FLOAT);

$min = ($min === false || $min === null) ? null : (float)$min;
$max = ($max === false || $max === null) ? null : (float)$max;

if ($min !== null && $max !== null && $min > $max) {
    [$min, $max] = [$max, $min];
}

if ($min !== null || $max !== null) {
    $productos_filtrados = array_filter($productos, function($p) use ($min, $max) {
        $precio = isset($p['PRECIO']) ? (float)$p['PRECIO'] : 0;
        if ($min !== null && $precio < $min) return false;
        if ($max !== null && $precio > $max) return false;
        return true;
    });
    $productos = array_values($productos_filtrados);
    $mensagem_catalogo = 'Resultados del filtro: ' . count($productos);
}
?>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 mb-4">
            <div class="card bg-dark border-secondary text-light p-3">
                <h4>Categorías</h4>
                <hr class="border-secondary">
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php?pagina=mods" class="text-warning text-decoration-none fw-bold">➜ Ver Todo</a></li>
                    <li class="mb-2"><a href="index.php?pagina=mods&categoria=Armas" class="text-light text-decoration-none">➜ Armas</a></li>
                    <li class="mb-2"><a href="index.php?pagina=mods&categoria=Trajes" class="text-light text-decoration-none">➜ Trajes</a></li>
                    <li class="mb-2"><a href="index.php?pagina=mods&categoria=Suministros" class="text-light text-decoration-none">➜ Suministros</a></li>
                </ul>
                
                <!-- Precio filter removed as requested -->
            </div>
        </div>

        <div class="col-md-9">
            <?php if (!empty($categoria_seleccionada)): ?>
                <h2 class="mb-4 text-warning">Catálogo — <?php echo htmlspecialchars($categoria_seleccionada); ?></h2>
            <?php else: ?>
                <h2 class="mb-4 text-warning">Catálogo de Suministros</h2>
            <?php endif; ?>
            
            <div class="row">
                <?php if (empty($productos)): ?>
                    <div class="col-12"><div class="alert alert-danger"><?php echo htmlspecialchars($mensagem_catalogo); ?></div></div>
                <?php else: ?>
                    
                    <?php foreach ($productos as $producto): 
                        $id_prod = (int)$producto['COD_PROD'];
                        $ya_lo_tiene = in_array($id_prod, $ids_comprados);
                        
                        $en_deseos = in_array($id_prod, $ids_deseados);
                        $btn_class = $en_deseos ? 'btn-danger text-white' : 'btn-outline-secondary';
                        $icon_class = $en_deseos ? 'bi-heart-fill' : 'bi-heart';
                    ?>

                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 bg-dark border-secondary text-light shadow-sm">
    
                            <img src="<?php echo htmlspecialchars($producto['IMAGEN'] ?? 'img/placeholder.jpg'); ?>" class="card-img-top border-bottom border-secondary" alt="<?php echo htmlspecialchars($producto['NOMBRE_PRODUCTO'] ?? 'Producto'); ?>" style="height: 200px; object-fit: cover; object-position: center;">

                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="text-warning me-3 fs-1"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <h5 class="card-title text-warning fw-bold mb-1"><?php echo htmlspecialchars($producto['NOMBRE_PRODUCTO'] ?? 'Sin Nombre'); ?></h5>
                                        <small class="text-secondary">ID Ref: <?php echo $id_prod; ?></small>
                                    </div>
                                </div>
                                
                                <p class="card-text text-light opacity-75 flex-grow-1">Suministro certificado por la Orden Espartana.</p>
                                <hr class="border-secondary my-3">

                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary">Precio:</span>
                                        <h4 class="fw-bold text-light mb-0">Bs. <?php echo number_format((float)($producto['PRECIO'] ?? 0), 2, '.', ','); ?></h4>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn <?php echo $btn_class; ?>" onclick="toggleWishlist(this, <?php echo $id_prod; ?>)">
                                            <i class="bi <?php echo $icon_class; ?>"></i>
                                        </button>

                                        <a href="index.php?pagina=detalle&id=<?php echo $id_prod; ?>" class="btn btn-outline-warning">Ver Especificaciones</a>
                                        
                                        <?php if ($ya_lo_tiene): ?>
                                            <button class="btn btn-success fw-bold d-flex align-items-center justify-content-center disabled" style="opacity: 1; cursor: not-allowed;">
                                                <i class="bi bi-check-circle-fill me-2"></i> ADQUIRIDO
                                            </button>
                                        <?php else: ?>
                                            <a href="index.php?pagina=mods&add_prod=<?php echo $id_prod; ?>" class="btn btn-warning fw-bold d-flex align-items-center justify-content-center">
                                                <i class="bi bi-cart-plus-fill me-2"></i> COMPRAR
                                            </a>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>