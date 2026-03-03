<?php
// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Lógica de agregar al carrito (se mantiene igual)
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

// --- NUEVO: OBTENER PRODUCTOS COMPRADOS POR EL USUARIO ---
$ids_comprados = []; // Lista vacía
$usuario_id = $_SESSION['id_usuario'] ?? null;

$ids_deseados=[];
if($usuario_id) {
    $api_deseos="http://localhost/MetroModsStore/app/controller/api_milista.php?usuario=" .$usuario_id;
    $json_deseos=@file_get_contents($api_deseos);

    if($json_deseos !== false) {
        $data_deseos = json_decode($json_deseos, true);
        if(isset($data_deseos['status']) && $data_deseos['status'] === 'success') {
            $ids_deseados = $data_deseos['data'];
        }
    }
}

if ($usuario_id) {
    // Ajusta la carpeta si es necesario
    $api_historial = "http://localhost/MetroModsStore/app/controller/api_miscompras.php?usuario=" . $usuario_id;
    $json_historial = @file_get_contents($api_historial);
    
    if ($json_historial !== false) {
        $data_hist = json_decode($json_historial, true);
        if (isset($data_hist['status']) && $data_hist['status'] === 'success') {
            // Extraemos solo los IDs de los productos comprados
            foreach ($data_hist['data'] as $compra) {
                $ids_comprados[] = (int)$compra['COD_PROD'];
            }
        }
    }
}
// ---------------------------------------------------------

/// CARGAR CATEGORÍAS
$api_cat_url = 'http://localhost/MetroModsStore/app/controller/api_categorias.php';
$lista_categorias = [];
$json_cat = @file_get_contents($api_cat_url);
if ($json_cat !== false) {
    $data_cat = json_decode($json_cat, true);
    if (isset($data_cat['status']) && $data_cat['status'] === 'success') {
        $lista_categorias = $data_cat['categorias'];
    }
}

// CAPTURAR EL FILTRO DE CATEGORÍA SI EL USUARIO HACE CLIC
$filtro_categoria = isset($_GET['cat']) ? (int)$_GET['cat'] : null; 

// CARGAR CATÁLOGO COMPLETO DE PRODUCTOS (¡No olvides esta parte!)
$api_url = 'http://localhost/MetroModsStore/app/controller/api_listarproductos.php'; 
$json_data = @file_get_contents($api_url); 

if ($json_data === false) {
    $mensagem_catalogo = 'Error al conectar con el servicio de productos.';
} else {
    $data = json_decode($json_data, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        
        // --- AQUÍ VA EL NUEVO BLOQUE DE FILTRADO ---
        if (!empty($data['productos'])) {
            $productos = $data['productos'];
            
            // Si el usuario seleccionó una categoría, filtramos la lista
            if ($filtro_categoria) {
                $filtrados = [];
                foreach ($productos as $p) {
                    if ((int)$p['ID_CATEGORIA'] === $filtro_categoria) {
                        $filtrados[] = $p;
                    }
                }
                $productos = $filtrados; // Reemplazamos la lista con los filtrados
            }
            
            // Actualizamos el mensaje dependiendo de si quedaron productos después de filtrar
            $mensagem_catalogo = empty($productos) ? 'No hay suministros en esta categoría.' : 'Suministros disponibles: ' . count($productos);
            
        } else {
            $mensagem_catalogo = 'No hay suministros en este momento.';
        }
        // --- FIN DEL BLOQUE DE FILTRADO ---

    } else {
        $mensagem_catalogo = 'Error en la API: ' . ($data['message'] ?? 'N/A');
    }
}
?>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 mb-4">
            <div class="card bg-dark border-secondary text-light p-3">
                <h4>Categorías</h4>
                <hr class="border-secondary">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php?pagina=mods" class="text-warning text-decoration-none fw-bold">➜ Ver Todo</a>
                    </li>
                    <?php foreach ($lista_categorias as $cat): ?>
                        <li class="mb-2">
                            <a href="index.php?pagina=mods&cat=<?php echo $cat['ID_CATEGORIA']; ?>" class="text-light text-decoration-none hover-warning">
                                ➜ <?php echo htmlspecialchars($cat['NOMBRE_CATEGORIA']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <h4 class="mt-4">Precio</h4>
                <hr class="border-secondary">
                <form action="index.php" method="GET">
                    <input type="hidden" name="pagina" value="mods">
                    <div class="d-flex gap-2">
                        <input type="number" name="min" class="form-control bg-secondary text-light border-0" placeholder="Min">
                        <input type="number" name="max" class="form-control bg-secondary text-light border-0" placeholder="Max">
                    </div>
                    <button type="submit" class="btn btn-outline-warning w-100 mt-3">Filtrar</button>
                </form>
            </div>
        </div>

        <div class="col-md-9">
            <h2 class="mb-4">Catálogo de Suministros</h2>
            
            <div class="row">
                <?php if (empty($productos)): ?>
                    <div class="col-12"><div class="alert alert-danger"><?php echo $mensagem_catalogo; ?></div></div>
                <?php else: ?>
                    
                    <?php foreach ($productos as $producto): 
                        // Intenta leer en mayúsculas, si no existe, prueba en minúsculas
                        $id_prod = isset($producto['COD_PROD']) ? (int)$producto['COD_PROD'] : (int)$producto['cod_prod'];
                        // Verificamos si YA TIENE este producto
                        $ya_lo_tiene = in_array($id_prod, $ids_comprados);

                        $en_deseos=in_array($id_prod, $ids_deseados);
                        $btn_class=$en_deseos ? 'btn-danger text-white' : 'btn-outline-secondary';
                        $icon_class = $en_deseos ? 'bi-heart-fill' : 'bi-heart';
                    ?>



                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 bg-dark border-secondary text-light shadow-sm">
    
                            <img src="<?php echo htmlspecialchars($producto['IMAGEN'] ?? 'img/placeholder.jpg'); ?>" class="card-img-top border-bottom border-secondary" alt="<?php echo htmlspecialchars($producto['NOMBRE_PRODUCTO']); ?>" style="height: 200px; object-fit: cover; object-position: center;">

                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="text-warning me-3 fs-1"><i class="bi bi-box-seam"></i></div>
                                    <div>
                                        <h5 class="card-title text-warning fw-bold mb-1"><?php echo htmlspecialchars($producto['NOMBRE_PRODUCTO']); ?></h5>
                                        <small class="text-secondary">ID Ref: <?php echo $id_prod; ?></small>
                                    </div>
                                </div>
                                
                                <p class="card-text text-light opacity-75 flex-grow-1">Suministro certificado por la Orden Espartana.</p>
                                <hr class="border-secondary my-3">

                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary">Precio:</span>
                                        <h4 class="fw-bold text-light mb-0">Bs. <?php echo number_format($producto['PRECIO'], 2, '.', ','); ?></h4>
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