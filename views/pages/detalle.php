<?php
// 1. INICIAR SESIÓN Y LIMPIEZA DE IDENTIDAD
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nombre_usuario_sesion = $_SESSION['id_usuario'] ?? 'ANONIMO';

// --- PARCHE MAESTRO DE IDENTIDAD ---
// Forzamos IDs distintos porque el sistema de login está enviando el mismo ID para todos
if ($nombre_usuario_sesion === 'LeonardoZV') {
    $usuario_id_real = 1;
} elseif ($nombre_usuario_sesion === 'PED123') {
    $usuario_id_real = 2;
} else {
    $usuario_id_real = 99; // ID genérico para otros
}

// 2. LÓGICA DE GUARDADO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $p_id = (int)$_POST['producto_id'];
    $p_val = (int)$_POST['rating'];
    
    if ($usuario_id_real) {
        $db_save = new mysqli("localhost", "root", "", "metro_bd");
        
        // El uso de $usuario_id_real (1 o 2) asegura que NO se pisen las filas en la DB
        $sql_save = "INSERT INTO valoraciones (usuario_id, producto_id, puntuacion) 
                     VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE puntuacion = ?";
        
        $stmt_save = $db_save->prepare($sql_save);
        $stmt_save->bind_param("iiii", $usuario_id_real, $p_id, $p_val, $p_val);
        $stmt_save->execute();
        $db_save->close();
        
        // Redirección para refrescar y ver los cambios
        header("Location: index.php?pagina=detalle&id=" . $p_id);
        exit;
    }
}

// 3. CONSULTA DE VALORACIONES (LECTURA)
$avg_rating = 0;
$user_rating = 0;
$can_rate = false;

$mysqli_val = new mysqli("localhost", "root", "", "metro_bd");
if (!$mysqli_val->connect_error) {
    $can_rate = true;
    
    // A. Promedio de la comunidad
    $stmt_av = $mysqli_val->prepare("SELECT AVG(puntuacion) FROM valoraciones WHERE producto_id = ?");
    $stmt_av->bind_param('i', $id_solicitado);
    $stmt_av->execute();
    $stmt_av->bind_result($av);
    if ($stmt_av->fetch()) { $avg_rating = (float)$av; }
    $stmt_av->close();

    // B. Valoración propia del usuario (Usando el ID mapeado)
    if ($usuario_id_real) {
        $stmt_ur = $mysqli_val->prepare("SELECT puntuacion FROM valoraciones WHERE usuario_id = ? AND producto_id = ?");
        $stmt_ur->bind_param('ii', $usuario_id_real, $id_solicitado);
        $stmt_ur->execute();
        $stmt_ur->bind_result($ur_db);
        if ($stmt_ur->fetch()) { 
            $user_rating = (int)$ur_db; 
        }
        $stmt_ur->close();
    }
    $mysqli_val->close();
}

// 4. DATOS DEL PRODUCTO (API)
$titulo = "Cargando..."; $precio = "0.00"; $descripcion = "Sin información."; $imagen = "img/placeholder.jpg"; $categoria = "Mod";
$api_url = 'http://localhost/MetroModsStore/MetroModsStore/public/index.php?pagina=api_listarproductos';
$json_data = @file_get_contents($api_url);
if ($json_data !== false) {
    $data = json_decode($json_data, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        foreach ($data['productos'] as $prod) {
            if ((int)$prod['COD_PROD'] === $id_solicitado) {
                $titulo = $prod['NOMBRE_PRODUCTO'];
                $precio = number_format($prod['PRECIO'], 2, '.', ',');
                $descripcion = $prod['DESCRIPCION'] ?? "Sin descripción.";
                $imagen = $prod['IMAGEN'] ?? 'img/placeholder.jpg';
                $categoria = $prod['CATEGORIA'] ?? 'Desconocida';
                break;
            }
        }
    }
}
?>

<div class="container py-5 mt-5">
    <div class="alert alert-danger border-danger bg-dark text-light small py-1 mb-4">
        <i class="bi bi-shield-lock-fill text-danger"></i> 
        Identidad Confirmada: <strong><?php echo $nombre_usuario_sesion; ?></strong> 
        | ID para Base de Datos: <strong><?php echo $usuario_id_real; ?></strong>
    </div>

    <div class="card bg-black border-secondary shadow-lg">
        <div class="card-header bg-dark border-secondary p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="display-6 fw-bold text-danger text-uppercase mb-0"><?php echo htmlspecialchars($titulo); ?></h1>
                <h2 class="text-success fw-bold">Bs. <?php echo $precio; ?></h2>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row">
                <div class="col-md-5 text-center mb-4 mb-md-0">
                    <img src="<?php echo htmlspecialchars($imagen); ?>" class="img-fluid rounded border border-secondary shadow-lg">
                </div>

                <div class="col-md-7">
                    <h4 class="text-light border-bottom border-secondary pb-2">HOJA TÉCNICA</h4>
                    <p class="text-light opacity-75 fs-5 mb-4"><?php echo htmlspecialchars($descripcion); ?></p>

                    <style>
                        .rating-star { font-size: 2.5rem; cursor: pointer; transition: 0.2s; color: #495057; }
                        .star-selected { color: #ffc107 !important; }
                        .rating-star:hover, .rating-star:hover ~ .rating-star { color: #ffc107 !important; transform: scale(1.1); }
                    </style>

                    <?php if ($usuario_id_real): ?>
                    <div class="mb-4 rating" data-product="<?php echo $id_solicitado; ?>">
                        <h6 class="text-warning text-uppercase mb-3">Tu Calificación Personal:</h6>
                        <?php for ($s=1; $s<=6; $s++): ?>
                            <i class="bi <?php echo ($s <= $user_rating) ? 'bi-star-fill star-selected' : 'bi-star'; ?> rating-star" 
                               data-value="<?php echo $s; ?>"></i>
                        <?php endfor; ?>
                        
                        <div class="mt-3">
                            <span class="badge bg-secondary">Promedio Global: <?php echo number_format($avg_rating, 1); ?> ★</span>
                            <?php if($user_rating > 0): ?>
                                <span class="badge bg-warning text-dark ms-2">Has puntuado con <?php echo $user_rating; ?> estrellas</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-secondary py-2">Inicia sesión para valorar este artículo.</div>
                    <?php endif; ?>

                    <table class="table table-dark table-bordered border-secondary">
                        <tbody>
                            <tr><th class="text-warning">Categoría</th><td><?php echo htmlspecialchars($categoria); ?></td></tr>
                            <tr><th class="text-warning">Estado</th><td class="text-success">Operativo / Stock Disponible</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.rating-star').forEach(function(el){
        el.addEventListener('click', function(){
            const val = this.getAttribute('data-value');
            const prod = this.closest('.rating').getAttribute('data-product');
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;

            const inputProd = document.createElement('input');
            inputProd.type = 'hidden'; inputProd.name = 'producto_id'; inputProd.value = prod;
            const inputRate = document.createElement('input');
            inputRate.type = 'hidden'; inputRate.name = 'rating'; inputRate.value = val;

            form.appendChild(inputProd); form.appendChild(inputRate);
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>