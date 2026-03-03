<?php
// views/pages/detalle.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id = $_SESSION['id_usuario'] ?? null;

// --- 1. OBTENER LISTA DE DESEOS ---
$ids_deseados = []; 
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

// --- 2. DEFINIR ESTADO DEL BOTÓN DESEOS ---
$en_deseos = in_array($id_solicitado, $ids_deseados);
$btn_class = $en_deseos ? 'btn-danger text-white' : 'btn-outline-secondary';
$icon_class = $en_deseos ? 'bi-heart-fill' : 'bi-heart';
$texto_btn = $en_deseos ? 'Eliminar de Deseos' : 'Añadir a Lista de Deseos';

// --- 3. BUSCAR DATOS DEL PRODUCTO ---
$titulo = "Producto No Encontrado";
$precio = "0.00";
$descripcion = "Sin información.";
$estado_producto = "Desconocido";
$imagen = "img/placeholder.jpg"; 
$categoria = "Suministros";      

$api_url = 'http://localhost/mods-metro-exodus/app/controller/api_listarproductos.php';
$json_data = @file_get_contents($api_url);

if ($json_data !== false) {
    $data = json_decode($json_data, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        foreach ($data['productos'] as $prod) {
            if ((int)$prod['COD_PROD'] === $id_solicitado) {
                $titulo = $prod['NOMBRE_PRODUCTO'];
                $precio = number_format($prod['PRECIO'], 2, '.', ',');
                
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

    <div class="card bg-black border-secondary shadow-lg mt-4">
        <div class="card-header bg-dark border-secondary p-4">
            <h4 class="text-warning mb-0" style="font-family: 'Oswald', sans-serif;">
                <i class="bi bi-chat-square-text-fill me-2"></i>BITÁCORA DE REPORTES
            </h4>
        </div>
        <div class="card-body p-4 p-md-5">
            
            <?php if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])): ?>
                
                <form id="formValoracion" class="mb-4">
                    <input type="hidden" name="cod_prod" value="<?php echo $id_solicitado; ?>">
                    
                    <div class="mb-3">
                        <label class="text-light mb-2 fw-bold">Añadir entrada a la bitácora:</label>
                        <textarea id="texto-comentario" name="comentario" class="form-control bg-dark text-light border-secondary" rows="3" required placeholder="Escribe tu opinión sobre este equipo..."></textarea>
                    </div>
                    
                    <button type="submit" id="btn-enviar" class="btn btn-warning fw-bold px-4">ENVIAR REPORTE</button>
                    <button type="button" id="btn-cancelar-edicion" class="btn btn-secondary fw-bold px-4 d-none" onclick="cancelarEdicion()">CANCELAR EDICIÓN</button>
                </form>
                
                <div id="mensaje-respuesta" class="fw-bold mb-4"></div>

            <?php else: ?>
                <div class="alert alert-dark border-warning text-light mb-4 shadow">
                    <i class="bi bi-shield-lock-fill text-warning me-2 fs-4"></i>
                    Comandante, la red de comunicaciones está encriptada. Debes 
                    <a href="index.php?pagina=login" class="text-warning fw-bold text-decoration-underline">iniciar sesión</a> 
                    para escribir en la bitácora.
                </div>
            <?php endif; ?>

            <div id="lista-comentarios">
                </div>
            
        </div>
    </div>
    </div>

<script>
// Guardamos el ID del usuario logueado en una variable JS 
const usuarioActual = "<?php echo $_SESSION['id_usuario'] ?? ''; ?>";
let editandoId = null; 

function cargarComentarios() {
    const listaDiv = document.getElementById("lista-comentarios");
    const idProducto = <?php echo $id_solicitado; ?>;
    
    listaDiv.innerHTML = '<p class="text-secondary"><i class="bi bi-hourglass-split"></i> Cargando transmisiones...</p>';
    
    fetch(`http://localhost/mods-metro-exodus/app/controller/api_leer_comentarios.php?id=${idProducto}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.data.length === 0) {
                listaDiv.innerHTML = '<p class="text-light opacity-50 fst-italic">Aún no hay reportes. Sé el primero.</p>';
                return;
            }
            
            let html = '';
            data.data.forEach(com => {
                let fecha = new Date(com.FECHA).toLocaleDateString();
                
                // Si el comentario es del usuario actual, le mostramos los botones
                let botonesAccion = '';
                if (com.USUARIO === usuarioActual) {
                    let textoSeguro = encodeURIComponent(com.COMENTARIO);
                    botonesAccion = `
                        <div>
                            <button class="btn btn-sm btn-outline-warning me-2" onclick="prepararEdicion(${com.ID_VALORACION}, '${textoSeguro}')"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarComentario(${com.ID_VALORACION})"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
                
                html += `
                <div class="card bg-dark border-secondary mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
                            <h6 class="text-warning mb-0 fw-bold"><i class="bi bi-person-fill me-2"></i>${com.NOMBRE}</h6>
                            <div class="d-flex align-items-center">
                                <small class="text-secondary me-3">${fecha}</small>
                                ${botonesAccion}
                            </div>
                        </div>
                        <p class="text-light mb-0" style="white-space: pre-wrap;">${com.COMENTARIO}</p>
                    </div>
                </div>
                `;
            });
            listaDiv.innerHTML = html;
        }
    });
}

// Función para ELIMINAR
function eliminarComentario(id) {
    if (confirm("¿Estás seguro de borrar este reporte de la bitácora?")) {
        let formData = new FormData();
        formData.append("accion", "eliminar");
        formData.append("id_valoracion", id);

        fetch("http://localhost/mods-metro-exodus/app/controller/api_gestionar_comentario.php", {
            method: "POST", body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') cargarComentarios();
            else alert(data.message);
        });
    }
}

// Función para preparar la EDICIÓN
function prepararEdicion(id, textoCodificado) {
    editandoId = id;
    document.getElementById("texto-comentario").value = decodeURIComponent(textoCodificado);
    document.getElementById("btn-enviar").innerText = "GUARDAR EDICIÓN";
    document.getElementById("btn-cancelar-edicion").classList.remove("d-none");
    document.getElementById("texto-comentario").focus();
}

// Función para CANCELAR la EDICIÓN
function cancelarEdicion() {
    editandoId = null;
    document.getElementById("formValoracion").reset();
    document.getElementById("btn-enviar").innerText = "ENVIAR REPORTE";
    document.getElementById("btn-cancelar-edicion").classList.add("d-none");
}

document.addEventListener("DOMContentLoaded", function() {
    cargarComentarios();
    
    const formValoracion = document.getElementById("formValoracion");
    const mensajeDiv = document.getElementById("mensaje-respuesta");
    
    if (formValoracion) {
        formValoracion.addEventListener("submit", function(e) {
            e.preventDefault(); 
            
            let formData = new FormData(formValoracion);
            let url = "http://localhost/mods-metro-exodus/app/controller/api_guardar_comentario.php";
            
            // Si estamos editando, cambiamos la URL y los datos que enviamos
            if (editandoId !== null) {
                url = "http://localhost/mods-metro-exodus/app/controller/api_gestionar_comentario.php";
                formData.append("accion", "editar");
                formData.append("id_valoracion", editandoId);
            }
            
            mensajeDiv.innerHTML = '<span class="text-secondary">Procesando...</span>';
            
            fetch(url, { method: "POST", body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    mensajeDiv.innerHTML = `<span class="text-success">${data.message}</span>`;
                    cancelarEdicion(); 
                    cargarComentarios(); 
                } else {
                    mensajeDiv.innerHTML = `<span class="text-danger">${data.message}</span>`;
                }
                setTimeout(() => mensajeDiv.innerHTML = '', 3000); 
            });
        });
    }
});
</script>