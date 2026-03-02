<?php
// views/pages/detalle.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id = $_SESSION['id_usuario'] ?? null;

// --- 1. OBTENER LISTA DE DESEOS (Corrección del Error) ---
$ids_deseados = []; // Inicializamos vacío para evitar error
if ($usuario_id) {
    $api_deseos = "http://localhost/mods-metro-exodus/app/controller/api_milista.php?usuario=" . $usuario_id;
    $json_deseos = @file_get_contents($api_deseos);
    
    if ($json_deseos !== false) {
        $data_deseos = json_decode($json_deseos, true);
        if (isset($data_deseos['status']) && $data_deseos['status'] === 'success') {
            $ids_deseados = $data_deseos['data'];
        }
    }
}

// --- 2. DEFINIR ESTADO DEL BOTÓN ---
// Usamos $id_solicitado en lugar de $id_prod
$en_deseos = in_array($id_solicitado, $ids_deseados);
$btn_class = $en_deseos ? 'btn-danger text-white' : 'btn-outline-secondary';
$icon_class = $en_deseos ? 'bi-heart-fill' : 'bi-heart';
$texto_btn = $en_deseos ? 'Eliminar de Deseos' : 'Añadir a Lista de Deseos';


// --- 3. BUSCAR DATOS DEL PRODUCTO ---
// --- 3. BUSCAR DATOS DEL PRODUCTO ---
$titulo = "Producto No Encontrado";
$precio = "0.00";
$descripcion = "Sin información.";
$estado_producto = "Desconocido";
$imagen = "img/placeholder.jpg"; // NUEVO: Variable para la imagen
$categoria = "Suministros";      // NUEVO: Variable para categoría

$api_url = 'http://localhost/mods-metro-exodus/app/controller/api_listarproductos.php';
$json_data = @file_get_contents($api_url);

if ($json_data !== false) {
    $data = json_decode($json_data, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        foreach ($data['productos'] as $prod) {
            if ((int)$prod['COD_PROD'] === $id_solicitado) {
                $titulo = $prod['NOMBRE_PRODUCTO'];
                $precio = number_format($prod['PRECIO'], 2, '.', ',');
                
                // AQUÍ USAMOS LOS DATOS REALES DE LA BD
                $descripcion = $prod['DESCRIPCION'] ?? "Sin descripción disponible.";
                $imagen = $prod['IMAGEN'] ?? 'img/placeholder.jpg';
                $categoria = $prod['CATEGORIA'] ?? 'Desconocida';
                
                $estado_producto = "Operativo (Grado A)";
                break;
            }
        }
    }
}

// --- 4. VERIFICAR SI YA LO COMPRÓ ---
$ya_lo_tiene = false;
if ($usuario_id) {
    $api_historial = "http://localhost/mods-metro-exodus/app/controller/api_miscompras.php?usuario=" . $usuario_id;
    $json_hist = @file_get_contents($api_historial);
    if ($json_hist) {
        $data_hist = json_decode($json_hist, true);
        if (isset($data_hist['data'])) {
            foreach ($data_hist['data'] as $compra) {
                if ((int)$compra['COD_PROD'] === $id_solicitado) {
                    $ya_lo_tiene = true;
                    break;
                }
            }
        }
    }
}
?>

<div class="container py-5">
    <a href="index.php?pagina=mods" class="text-secondary text-decoration-none mb-4 d-inline-block hover-warning">
        <i class="bi bi-arrow-left"></i> Volver al Catálogo
    </a>

    <div class="card bg-black border-secondary shadow-lg">
        
        <div class="card-header bg-dark border-secondary p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-warning mb-2">HOJA DE DATOS TÉCNICOS // REF: <?php echo $id_solicitado; ?>-METRO</h6>
                    <h1 class="display-5 fw-bold text-danger mb-0 text-uppercase"><?php echo htmlspecialchars($titulo); ?></h1>
                </div>
                <div class="text-end">
                    <h2 class="text-success fw-bold">Bs. <?php echo $precio; ?></h2>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row">
                
                <div class="col-md-5 mb-4 mb-md-0 text-center">
                    <img src="<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars($titulo); ?>" class="img-fluid rounded border border-secondary shadow-lg" style="object-fit: cover; max-height: 400px; width: 100%;">
                </div>

                <div class="col-md-7 d-flex flex-column">
                    <div class="mb-4">
                        <h4 class="text-light mb-3 border-bottom border-secondary pb-2">Descripción del Artículo</h4>
                        <p class="text-light fs-5 opacity-75"><?php echo htmlspecialchars($descripcion); ?></p>
                    </div>

                    <table class="table table-dark table-bordered border-secondary mb-5">
                        <tbody>
                            <tr><th class="text-warning w-25">Categoría</th><td><?php echo htmlspecialchars($categoria); ?></td></tr>
                            <tr><th class="text-warning w-25">Disponibilidad</th><td class="text-success">Inmediata</td></tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end gap-3 mt-auto flex-wrap">
                        <button type="button" class="btn <?php echo $btn_class; ?> px-4" onclick="toggleWishlist(this, <?php echo $id_solicitado; ?>)">
                            <i class="bi <?php echo $icon_class; ?>"></i> <?php echo $texto_btn; ?>
                        </button>
                        
                        <?php if ($ya_lo_tiene): ?>
                            <button class="btn btn-success btn-lg fw-bold px-4 disabled" style="opacity: 1; cursor: not-allowed;">
                                <i class="bi bi-check-circle-fill me-2"></i> YA ADQUIRIDO
                            </button>
                        <?php else: ?>
                            <a href="index.php?pagina=mods&add_prod=<?php echo $id_solicitado; ?>" class="btn btn-warning btn-lg fw-bold px-4">
                                <i class="bi bi-cart-plus-fill me-2"></i> AÑADIR AL CARRITO
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>