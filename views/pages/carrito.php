<?php
// Inicia la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// --- LÓGICA PARA ELIMINAR ITEM ---
if (isset($_GET['remove_id']) && is_numeric($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];
    if (isset($_SESSION['carrito'][$remove_id])) {
        unset($_SESSION['carrito'][$remove_id]);
    }
    header('Location: index.php?pagina=carrito');
    exit(); 
}
// ---------------------------------

$produtos_no_carrinho = $_SESSION['carrito'];
$lista_ids_para_api = array_keys($produtos_no_carrinho);
$produtos_detalhados = [];
$produtos_para_venda = []; 
$subtotal = 0;
$total = 0;
$mensagem_carrinho = '';

if (empty($lista_ids_para_api)) {
    $mensagem_carrinho = 'Tu carrito está vacío. ¡Es hora de equiparte!';
    unset($_SESSION['dados_venda']);
} else {
    // CONEXIÓN DIRECTA Y SEGURA A LA BASE DE DATOS (A prueba de fallos)
    $hostname = "localhost";
    $basedatos = "metro_bd";
    $usuario_db = "root";
    $contrasena_db = "";

    $mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

    if ($mysqli->connect_error) {
        $mensagem_carrinho = "Error de conexión a la base de datos.";
    } else {
        // Prepara los IDs de forma segura (Ej: 1, 2, 3)
        $ids_seguros = implode(',', array_map('intval', $lista_ids_para_api));
        
        // Consulta directa a la tabla PRODUCTOS
        $sql = "SELECT COD_PROD, NOMBRE_PRODUCTO, PRECIO, IMAGEN FROM PRODUCTOS WHERE COD_PROD IN ($ids_seguros)";
        $resultado = $mysqli->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            while ($prod = $resultado->fetch_assoc()) {
                $id = $prod['COD_PROD'];
                $quantidade = 1; 
                $preco = (float)$prod['PRECIO'];
                $total_item = $preco * $quantidade;
                
                $prod['QUANTIDADE'] = $quantidade;
                $prod['TOTAL_ITEM'] = $total_item;
                
                $produtos_detalhados[$id] = $prod;
                
                $produtos_para_venda[] = [
                    'id' => (int)$id,
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                    'subtotal' => $total_item
                ];
                
                $subtotal += $total_item;
            }
            $total = $subtotal; 
            
            $_SESSION['dados_venda'] = [
                'produtos' => $produtos_para_venda,
                'total' => $total,
            ];
        } else {
            $mensagem_carrinho = 'Error: No se encontraron las armas en el inventario general.';
        }
        $mysqli->close();
    }
}
?>

<div class="container py-5">
    <h2 class="display-5 text-warning fw-bold mb-5 text-center">Tu Equipamiento</h2>

    <?php if (!empty($mensagem_carrinho)): ?>
        <div class="alert alert-info text-center bg-dark border-warning text-warning" role="alert">
            <i class="bi bi-info-circle me-2"></i> <?php echo htmlspecialchars($mensagem_carrinho); ?>
            <p class="mt-2 mb-0"><a href="index.php?pagina=mods" class="text-warning fw-bold text-decoration-underline">Ir al Catálogo</a></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($produtos_detalhados)): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-secondary border-secondary">
                                    <th scope="col" class="p-4">Producto</th>
                                    <th scope="col">Precio Unitario</th>
                                    <th scope="col" class="text-end p-4">Total (1 Unidad)</th>
                                    <th scope="col" class="p-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_detalhados as $id => $item): ?>
                                <tr>
                                    <td class="p-4">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['IMAGEN'] ?? 'img/placeholder.jpg'); ?>" class="rounded border border-secondary me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0 text-warning"><?php echo htmlspecialchars($item['NOMBRE_PRODUCTO']); ?></h6>
                                                <small class="mb-0 text-secondary fs-6">ID: <?php echo htmlspecialchars($id); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Bs. <?php echo number_format($item['PRECIO'], 2, '.', ','); ?></td>
                                    <td class="text-end p-4 fw-bold">Bs. <?php echo number_format($item['TOTAL_ITEM'], 2, '.', ','); ?></td>
                                    <td>
                                        <a href="index.php?pagina=carrito&remove_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar de tu equipamiento">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <a href="index.php?pagina=mods" class="text-secondary text-decoration-none">
                &larr; Seguir buscando suministros
            </a>
            
        </div>

        <div class="col-lg-4">
            <div class="card bg-black border-secondary p-4 sticky-top" style="top: 20px;">
                <h4 class="mb-4">Resumen</h4>
                
                <div class="d-flex justify-content-between mb-2 text-secondary">
                    <span>Subtotal</span>
                    <span>Bs. <?php echo number_format($subtotal, 2, '.', ','); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4 text-success">
                    <span>Descuento</span>
                    <span>- Bs. 0.00</span>
                </div>
                <hr class="border-secondary">
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="h4 text-light">Total a Pagar</span>
                    <span class="h4 text-warning fw-bold">Bs. <?php echo number_format($total, 2, '.', ','); ?></span>
                </div>

                <form method="POST" action="index.php?pagina=checkout"> 
                    <button type="submit" class="btn btn-warning w-100 fw-bold btn-lg mb-3">CONFIRMAR COMPRA</button>
                </form>
                
                <div class="text-center">
                   <small class="text-muted">Pagos seguros vía Hansa</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>