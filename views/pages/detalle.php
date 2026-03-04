<?php
// views/pages/detalle.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id = $_SESSION['id_usuario'] ?? $_SESSION['usuario_logueado'] ?? null;

// --- VARIABLES POR DEFECTO ---
$titulo = "Producto No Encontrado"; $precio = "0.00"; $descripcion = "Sin información.";
$estado_producto = "Desconocido"; $imagen = "img/placeholder.jpg"; $categoria = "Suministros";
$en_deseos = false; $ya_lo_tiene = false;
$avg_rating = 0; $user_rating = 0;

// --- CONEXIÓN DIRECTA Y ÚNICA A LA BASE DE DATOS ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario_db = "root";
$contrasena_db = "";

$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

if (!$mysqli->connect_error && $id_solicitado > 0) {
    
    // 1. OBTENER DATOS DEL PRODUCTO
    $sql_p = "SELECT P.*, C.NOMBRE_CATEGORIA AS CATEGORIA_TXT 
              FROM PRODUCTOS P 
              LEFT JOIN CATEGORIAS C ON P.ID_CATEGORIA = C.ID_CATEGORIA 
              WHERE P.COD_PROD = ?";
    $stmt_p = $mysqli->prepare($sql_p);
    if ($stmt_p) {
        $stmt_p->bind_param("i", $id_solicitado);
        $stmt_p->execute();
        $res_p = $stmt_p->get_result();
        if ($prod = $res_p->fetch_assoc()) {
            $titulo = $prod['NOMBRE_PRODUCTO'];
            $precio = number_format($prod['PRECIO'], 2, '.', ',');
            $descripcion = $prod['DESCRIPCION'] ?? "Sin descripción disponible.";
            $imagen = $prod['IMAGEN'] ?? 'img/placeholder.jpg';
            $categoria = $prod['CATEGORIA_TXT'] ?? 'Desconocida';
            $estado_producto = "Operativo (Grado A)";
        }
        $stmt_p->close();
    }

    // 2. OBTENER VALORACIONES (Promedio de estrellas)
    $stmt_av = $mysqli->prepare("SELECT AVG(puntuacion) FROM valoraciones WHERE producto_id = ?");
    if($stmt_av) {
        $stmt_av->bind_param('i', $id_solicitado);
        $stmt_av->execute();
        $stmt_av->bind_result($av);
        if ($stmt_av->fetch()) { $avg_rating = (float)$av; }
        $stmt_av->close();
    }

    // 3. DATOS DEL USUARIO LOGUEADO
    if ($usuario_id) {
        $stmt_ur = $mysqli->prepare("SELECT puntuacion FROM valoraciones WHERE usuario_id = ? AND producto_id = ?");
        if($stmt_ur) {
            $stmt_ur->bind_param('si', $usuario_id, $id_solicitado);
            $stmt_ur->execute();
            $stmt_ur->bind_result($ur_db);
            if ($stmt_ur->fetch()) { $user_rating = (int)$ur_db; }
            $stmt_ur->close();
        }

        $stmt_d = $mysqli->prepare("SELECT 1 FROM LISTA_DESEOS WHERE USUARIO = ? AND COD_PROD = ?");
        if ($stmt_d) {
            $stmt_d->bind_param("si", $usuario_id, $id_solicitado);
            $stmt_d->execute();
            if ($stmt_d->get_result()->num_rows > 0) $en_deseos = true;
            $stmt_d->close();
        }
        $stmt_c = $mysqli->prepare("SELECT 1 FROM COMPRA WHERE USUARIO = ? AND COD_PROD = ?");
        if ($stmt_c) {
            $stmt_c->bind_param("si", $usuario_id, $id_solicitado);
            $stmt_c->execute();
            if ($stmt_c->get_result()->num_rows > 0) $ya_lo_tiene = true;
            $stmt_c->close();
        }
    }
    $mysqli->close();
}

$btn_class = $en_deseos ? 'btn-danger text-white' : 'btn-outline-secondary';
$icon_class = $en_deseos ? 'bi-heart-fill' : 'bi-heart';
$texto_btn = $en_deseos ? 'ELIMINAR DE DESEOS' : 'AÑADIR A LISTA DE DESEOS';
?>

<div class="container py-5 mt-4">
    <a href="index.php?pagina=mods" class="text-secondary text-decoration-none mb-4 d-inline-block hover-warning">
        <i class="bi bi-arrow-left"></i> Volver al Catálogo
    </a>

    <div class="card bg-black border-secondary shadow-lg mb-4">
        <div class="card-header bg-dark border-secondary p-4">
            <div class="d-flex justify-content-between align-items-start text-white">
                <div>
                    <h6 class="text-warning mb-2">HOJA DE DATOS TÉCNICOS // REF: <?php echo $id_solicitado; ?>-METRO</h6>
                    <h1 class="display-5 fw-bold text-danger mb-0 text-uppercase"><?php echo htmlspecialchars($titulo); ?></h1>
                </div>
                <div class="text-end">
                    <h2 class="text-success fw-bold">BS. <?php echo $precio; ?></h2>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row">
                <div class="col-md-5 mb-4 mb-md-0 text-center">
                    <img src="<?php echo htmlspecialchars($imagen); ?>" class="img-fluid rounded border border-secondary shadow-lg" style="object-fit: cover; max-height: 400px; width: 100%;">
                </div>

                <div class="col-md-7 d-flex flex-column">
                    <h4 class="text-light mb-3 border-bottom border-secondary pb-2 text-uppercase">Descripción del Artículo</h4>
                    <p class="text-light fs-5 opacity-75 mb-4"><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>

                    <style>
                        /* El contenedor se invierte visualmente para que el truco del hover funcione */
                        .rating-container { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
                        /* Usamos bi-star-fill siempre, y solo cambiamos el color. Gris por defecto. */
                        .rating-star { font-size: 2rem; cursor: pointer; transition: 0.2s; color: #495057; }
                        /* Color amarillo para la selección guardada */
                        .star-selected { color: #ffc107 !important; }
                        /* Efecto hover interactivo */
                        .rating-container > .rating-star:hover, 
                        .rating-container > .rating-star:hover ~ .rating-star { color: #ffc107 !important; transform: scale(1.1); }
                    </style>

                    <div class="mb-4 bg-dark p-3 rounded border border-secondary">
                        <h6 class="text-warning text-uppercase mb-2">Calificación del Equipo:</h6>
                        <div class="d-flex align-items-center flex-wrap">
                            <div class="rating-container rating" data-product="<?php echo $id_solicitado; ?>">
                                <?php 
                                // BUCLE INVERSO: Imprimimos del 5 al 1 (CSS le da la vuelta visualmente)
                                for ($s=5; $s>=1; $s--): 
                                ?>
                                    <i class="bi bi-star-fill rating-star <?php echo ($s <= $user_rating) ? 'star-selected' : ''; ?>" 
                                       data-value="<?php echo $s; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="badge bg-secondary ms-3 fs-6 mt-2 mt-md-0">Promedio: <?php echo number_format($avg_rating, 1); ?> ★</span>
                        </div>
                        
                        <div id="msg-login-estrellas" class="alert alert-danger mt-3 d-none p-2 mb-0 border-danger text-light bg-black">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Debes <a href="index.php?pagina=login" class="alert-link text-warning">iniciar sesión</a> para valorar este artículo.
                        </div>
                    </div>

                    <table class="table table-dark table-bordered border-secondary mb-4">
                        <tbody>
                            <tr><th class="text-warning w-25">CATEGORÍA</th><td><?php echo htmlspecialchars($categoria); ?></td></tr>
                            <tr><th class="text-warning w-25">ESTADO</th><td class="text-success"><?php echo $estado_producto; ?></td></tr>
                        </tbody>
                    </table>

                    <div class="d-flex gap-3 mt-auto flex-wrap">
                        <button class="btn <?php echo $btn_class; ?> px-4 fw-bold" onclick="toggleWishlist(this, <?php echo $id_solicitado; ?>)">
                            <i class="bi <?php echo $icon_class; ?>"></i> <?php echo $texto_btn; ?>
                        </button>
                        <?php if ($ya_lo_tiene): ?>
                            <button class="btn btn-success fw-bold px-4 disabled" style="opacity: 1;">ADQUIRIDO</button>
                        <?php else: ?>
                            <a href="index.php?pagina=mods&add_prod=<?php echo $id_solicitado; ?>" class="btn btn-warning fw-bold px-4">AÑADIR AL CARRITO</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-black border-secondary shadow-lg">
        <div class="card-header bg-dark border-secondary p-4">
            <h4 class="text-warning mb-0"><i class="bi bi-chat-square-text-fill me-2"></i>BITÁCORA DE REPORTES</h4>
        </div>
        <div class="card-body p-4">
            <?php if (!$usuario_id): ?>
                <div class="alert alert-warning border-2 text-dark fw-bold mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Debes <a href="index.php?pagina=login" class="alert-link fw-bold">iniciar sesión</a> para dejar un reporte.
                </div>
            <?php else: ?>
                <form id="formBitacora" class="mb-4">
                    <input type="hidden" name="cod_prod" value="<?php echo $id_solicitado; ?>">
                    <textarea name="comentario" class="form-control bg-dark text-light border-secondary mb-3" rows="3" required placeholder="Escribir reporte de equipo..."></textarea>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-send-fill me-2"></i>ENVIAR REPORTE</button>
                </form>
            <?php endif; ?>
            <div id="lista-reportes"></div>
        </div>
    </div>
</div>

<script>
const userActual = "<?php echo $usuario_id; ?>";

function cargarReportes() {
    const contenedor = document.getElementById("lista-reportes");
    fetch(`index.php?pagina=api_leer_comentarios&id=<?php echo $id_solicitado; ?>`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            let html = '';
            data.data.forEach(rep => {
                const esDelUsuario = (rep.USUARIO === userActual);
                const borde = esDelUsuario ? 'border-warning' : 'border-secondary';
                html += `
                <div class="card bg-dark border-2 ${borde} mb-3 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="text-warning text-uppercase fw-bold mb-0">
                                <i class="bi bi-person-fill me-2"></i>${rep.NOMBRE || rep.USUARIO}
                            </h6>
                            <small class="text-secondary">${rep.FECHA}</small>
                        </div>
                        <p class="text-light mb-0" style="white-space: pre-wrap;">${rep.COMENTARIO}</p>
                    </div>
                </div>`;
            });
            contenedor.innerHTML = html || '<p class="text-secondary fst-italic">Sin reportes en la bitácora.</p>';
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    cargarReportes();

    // ENVÍO DE VALORACIÓN (ESTRELLAS DE LEONARDO)
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('click', function() {
            // VERIFICACIÓN DE LOGIN
            if(!userActual) {
                // Muestra el mensaje de error bonito de Bootstrap
                document.getElementById('msg-login-estrellas').classList.remove('d-none');
                return; // Detiene la ejecución
            }

            const val = this.getAttribute('data-value');
            const formData = new FormData();
            formData.append('producto_id', <?php echo $id_solicitado; ?>);
            formData.append('rating', val);

            fetch('index.php?pagina=api_valorar', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') location.reload();
                else alert("Error: " + data.message);
            });
        });
    });

    // ENVÍO DE REPORTE (BITÁCORA)
    document.getElementById("formBitacora")?.addEventListener("submit", (e) => {
        e.preventDefault();
        fetch('index.php?pagina=api_guardar_comentario', {
            method: 'POST',
            body: new FormData(e.target)
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') { 
                e.target.reset(); 
                cargarReportes(); 
            } else {
                alert(data.message);
            }
        });
    });
});
</script>