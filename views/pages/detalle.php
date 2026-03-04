<?php
// views/pages/detalle.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Sincronizamos con el nombre de tu variable de sesión
$usuario_id = $_SESSION['id_usuario'] ?? $_SESSION['usuario_logueado'] ?? null;

// --- VARIABLES POR DEFECTO ---
$titulo = "Producto No Encontrado";
$precio = "0.00";
$descripcion = "Sin información.";
$estado_producto = "Desconocido";
$imagen = "img/placeholder.jpg"; 
$categoria = "Suministros";
$en_deseos = false;
$ya_lo_tiene = false;

// --- CONEXIÓN DIRECTA A LA BASE DE DATOS (Rápida y segura) ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario_db = "root";
$contrasena_db = "";

$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);

if (!$mysqli->connect_error && $id_solicitado > 0) {
    
    // 1. OBTENER DATOS DEL PRODUCTO (Con JOIN a CATEGORIAS)
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

    // 2. VERIFICAR SI ESTÁ EN DESEOS O YA FUE COMPRADO
    if ($usuario_id) {
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

<div class="container py-5">
    <a href="index.php?pagina=mods" class="text-secondary text-decoration-none mb-4 d-inline-block hover-warning">
        <i class="bi bi-arrow-left"></i> Volver al Catálogo
    </a>

    <div class="card bg-black border-secondary shadow-lg">
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
                    <div class="mb-4">
                        <h4 class="text-light mb-3 border-bottom border-secondary pb-2 text-uppercase">Descripción del Artículo</h4>
                        <p class="text-light fs-5 opacity-75"><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
                    </div>

                    <table class="table table-dark table-bordered border-secondary mb-5">
                        <tbody>
                            <tr><th class="text-warning w-25">CATEGORÍA</th><td><?php echo htmlspecialchars($categoria); ?></td></tr>
                            <tr><th class="text-warning w-25">ESTADO</th><td class="text-success"><?php echo $estado_producto; ?></td></tr>
                        </tbody>
                    </table>

                    <div class="d-flex gap-3 mt-auto">
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

    <div class="card bg-black border-secondary shadow-lg mt-4">
        <div class="card-header bg-dark border-secondary p-4">
            <h4 class="text-warning mb-0"><i class="bi bi-chat-square-text-fill me-2"></i>BITÁCORA DE REPORTES</h4>
        </div>
        <div class="card-body p-4">
            <!-- SECCIÓN FORMULARIO -->
            <?php if (!$usuario_id): ?>
                <!-- NO LOGUEADO -->
                <div class="alert alert-warning border-2 text-dark fw-bold mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Debes <a href="index.php?pagina=login" class="alert-link fw-bold">iniciar sesión</a> para dejar un reporte.
                </div>
            <?php elseif (!$ya_lo_tiene): ?>
                <!-- NO COMPRÓ -->
                <div class="alert alert-danger border-2 text-light mb-4">
                    <i class="bi bi-lock-fill me-2"></i>
                    Debes adquirir este artículo primero para dejar un reporte.
                </div>
            <?php else: ?>
                <!-- COMPRÓ Y LOGUEADO -->
                <form id="formBitacora" class="mb-4">
                    <input type="hidden" name="cod_prod" value="<?php echo $id_solicitado; ?>">
                    <textarea id="texto-reporte" name="comentario" 
                              class="form-control bg-secondary text-light border-secondary mb-3" 
                              rows="3" required 
                              placeholder="Escribir reporte de equipo..."></textarea>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-send-fill me-2"></i>ENVIAR REPORTE
                    </button>
                </form>
            <?php endif; ?>
            
            <!-- SECCIÓN COMENTARIOS -->
            <div id="lista-reportes" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
let comentariosExistentes = [];
let usuarioActual = '<?php echo htmlspecialchars($usuario_id ?? ""); ?>';

function cargarReportes() {
    const contenedor = document.getElementById("lista-reportes");
    fetch(`/index.php?pagina=api_leer_comentarios&id=<?php echo $id_solicitado; ?>`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                comentariosExistentes = data.data;
                validarFormulario();
                
                let html = '<div class="border-top border-secondary pt-3">';
                data.data.forEach(rep => {
                    // Determinar si es del usuario actual
                    const esDelUsuario = (rep.USUARIO === usuarioActual);
                    const claseUsuario = esDelUsuario ? 'border-warning' : 'border-secondary';
                    const iconoUsuario = esDelUsuario ? 'bi-star-fill text-warning' : 'bi-person-circle';
                    
                    html += `
                    <div class="card bg-dark border-2 ${claseUsuario} mb-3 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-warning text-uppercase fw-bold mb-0">
                                    <i class="bi ${iconoUsuario} me-2"></i>${rep.NOMBRE || rep.USUARIO}
                                </h6>
                                <small class="text-secondary">${rep.FECHA}</small>
                            </div>
                            <p class="text-light mb-0" style="white-space: pre-wrap;">${rep.COMENTARIO}</p>
                        </div>
                    </div>`;
                });
                html += '</div>';
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = '<p class="text-secondary opacity-75 fst-italic text-center my-3">📭 Sin reportes registrados en la bitácora.</p>';
            }
        })
        .catch(err => {
            console.error('Error cargando reportes:', err);
            contenedor.innerHTML = '<p class="text-danger text-center">⚠️ Error al cargar reportes</p>';
        });
}

function validarFormulario() {
    const formBitacora = document.getElementById("formBitacora");
    if (!formBitacora || !usuarioActual) return;
    
    // Verificar si el usuario actual ya comentó
    const yaComento = comentariosExistentes.some(c => c.USUARIO === usuarioActual);
    
    if (yaComento) {
        // Deshabilitar formulario y mostrar mensaje
        const textarea = formBitacora.querySelector('textarea');
        const btn = formBitacora.querySelector('button');
        
        textarea.disabled = true;
        btn.disabled = true;
        btn.classList.add('opacity-50');
        
        const alerta = document.createElement('div');
        alerta.className = 'alert alert-info border-2 text-light mb-3';
        alerta.innerHTML = '<i class="bi bi-info-circle me-2"></i>Ya dejaste un reporte para este artículo.';
        formBitacora.parentNode.insertBefore(alerta, formBitacora);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    cargarReportes();
    const formBitacora = document.getElementById("formBitacora");
    
    if (formBitacora) {
        formBitacora.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const btn = formBitacora.querySelector('button[type="submit"]');
            const btnOriginalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ Guardando...';
            
            fetch('/index.php?pagina=api_guardar_comentario', {
                method: 'POST',
                body: new FormData(formBitacora)
            })
            .then(res => res.json())
            .then(data => {
                console.log('Respuesta del servidor:', data);
                
                if (data.status === 'success') {
                    // ÉXITO
                    alert('✓ ' + data.message);
                    formBitacora.reset();
                    cargarReportes(); // Recargar lista
                } else {
                    // ERROR DEL SERVIDOR
                    alert('✗ ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(err => {
                console.error('Error en fetch:', err);
                alert('✗ Error de conexión al guardar el comentario');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = btnOriginalText;
            });
        });
    }
});
</script>