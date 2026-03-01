<?php
// 1. INICIO DE SESIÓN Y DATOS
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$nombre_sesion = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : '';
$usuario_sesion = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : '';

// Lógica nombre
if (!empty($nombre_sesion)) {
    $nombre_mostrar = strtoupper($nombre_sesion) . " (" . strtoupper($usuario_sesion) . ")";
} else {
    $nombre_mostrar = strtoupper($usuario_sesion);
}
$usuario_real = strtoupper($usuario_sesion);
$inicial = substr($nombre_mostrar, 0, 1);

// --- A. HISTORIAL DE COMPRAS (Ya funcionaba) ---
$historial_compras = [];
if (!empty($usuario_sesion)) {
    $api_url = "http://localhost/mods-metro-exodus/app/controller/api_miscompras.php?usuario=" . $usuario_sesion;
    $json_data = @file_get_contents($api_url);
    if ($json_data !== false) {
        $data = json_decode($json_data, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            $historial_compras = $data['data'];
        }
    }
}

// --- B. LISTA DE DESEOS (NUEVA LÓGICA DINÁMICA) ---
$lista_deseos_detalles = [];
if (!empty($usuario_sesion)) {
    // 1. Obtener IDs de deseos
    $api_deseos_ids = "http://localhost/mods-metro-exodus/app/controller/api_milista.php?usuario=" . $usuario_sesion;
    $json_ids = @file_get_contents($api_deseos_ids);
    
    if ($json_ids !== false) {
        $data_ids = json_decode($json_ids, true);
        if (isset($data_ids['status']) && $data_ids['status'] === 'success' && !empty($data_ids['data'])) {
            $mis_ids = $data_ids['data']; // Array de IDs [1, 3]
            
            // 2. Obtener Catálogo Completo para buscar nombres/precios
            // (Esto no es lo más eficiente pero es lo más rápido sin crear otra API)
            $api_catalogo = "http://localhost/mods-metro-exodus/app/controller/api_listarproductos.php";
            $json_cat = @file_get_contents($api_catalogo);
            
            if ($json_cat !== false) {
                $data_cat = json_decode($json_cat, true);
                if (isset($data_cat['produtos'])) {
                    foreach ($data_cat['produtos'] as $prod) {
                        if (in_array((int)$prod['COD_PROD'], $mis_ids)) {
                            $lista_deseos_detalles[] = $prod;
                        }
                    }
                }
            }
        }
    }
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
        
        <div class="col-lg-6 mb-4">
            <div class="card bg-black border-secondary h-100">
                <div class="card-header bg-dark border-secondary">
                    <h4 class="text-warning mb-0"><i class="bi bi-box-seam-fill me-2"></i>Compras Realizadas</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($historial_compras)): ?>
                        <p class="text-secondary text-center mt-4">Aún no has realizado compras.</p>
                    <?php else: ?>
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr class="text-secondary mb-4">
                                    <th class="text-warning">Producto</th>
                                    <th class="text-warning">Fecha</th>
                                    <th class="text-warning text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial_compras as $compra): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($compra['NOMBRE_PRODUCTO']); ?></td>
                                    <td class="text-muted small"><?php echo date("d/M/Y", strtotime($compra['FECHA'])); ?></td>
                                    <td class="text-end"><span class="badge bg-success">Entregado</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card bg-black border-secondary h-100">
                <div class="card-header bg-dark border-secondary">
                    <h4 class="text-warning mb-0"><i class="bi bi-heart-fill me-2"></i>Lista de Deseos</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (empty($lista_deseos_detalles)): ?>
                        <p class="text-secondary text-center mt-4">Tu lista de deseos está vacía.</p>
                        <p class="text-secondary small text-center">Agrega más ítems desde el catálogo.</p>
                    <?php else: ?>
                        
                        <?php foreach ($lista_deseos_detalles as $item): ?>
                        <div class="d-flex align-items-center border-bottom border-secondary pb-3 mb-3" id="deseo-<?php echo $item['COD_PROD']; ?>">
                            
                            <div class="text-warning fs-1 me-3"><i class="bi bi-box2-heart"></i></div>
                            
                            <div class="flex-grow-1">
                                <h6 class="text-warning mb-0"><?php echo htmlspecialchars($item['NOMBRE_PRODUCTO']); ?></h6>
                                <small class="text-success">Bs. <?php echo number_format($item['PRECIO'], 2); ?></small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="index.php?pagina=detalle&id=<?php echo $item['COD_PROD']; ?>" class="btn btn-sm btn-outline-warning">Ver</a>
                                
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarDeseoPerfil(this, <?php echo $item['COD_PROD']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>

                    <?php endif; ?>
                    
                </div>
            </div>
        </div>

    </div>
</div>