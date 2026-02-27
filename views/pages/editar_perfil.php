<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario'])) { echo "<script>window.location.href='index.php?pagina=login';</script>"; exit; }

// Datos actuales
$id_usuario = $_SESSION['id_usuario'];
$nombre_actual = $_SESSION['nombre_usuario'];
$correo_actual = ""; 

$hostname = "localhost"; $basedatos = "metro3"; $usuario_db = "root"; $contrasena_db = "";
$mysqli = new mysqli($hostname, $usuario_db, $contrasena_db, $basedatos);
if (!$mysqli->connect_error) {
    $sql = "SELECT CORREO FROM CLIENTE WHERE USUARIO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($fila = $res->fetch_assoc()) { $correo_actual = $fila['CORREO']; }
    $stmt->close(); $mysqli->close();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black border-secondary d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">ACTUALIZAR DATOS</h3>
                    <a href="index.php?pagina=perfil" class="btn btn-sm btn-outline-secondary">Volver al Perfil</a>
                </div>
                
                <div class="card-body p-4">
                    <form id="formEditar" action="index.php?pagina=api_actualizar" method="POST">
                        
                        <div id="mensajeRespuesta" class="mb-3"></div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                            <h5 class="text-warning mb-3 border-bottom border-secondary pb-2">Información de Cuenta</h5>
                            
                            <div class="mb-3">
                                <label class="form-label text-secondary">Nombre en Clave (Usuario)</label>
                                <div class="input-group">
                                    <input type="text" id="inputUser" name="usuario_id" class="form-control bg-secondary text-light border-0" value="<?php echo $id_usuario; ?>" readonly required>
                                    <button class="btn btn-outline-warning" type="button" id="btnToggleUser" onclick="toggleUserEdit()">
                                        <i class="bi bi-pencil-fill" id="iconUser"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-none" id="userHelp">Cambiar esto cerrará tu sesión en otros dispositivos.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary">Rango Actual</label>
                                <input type="text" class="form-control bg-black text-warning border-secondary fw-bold" value="Spartan Ranger" disabled>
                            </div>
                        </div>

                            <div class="col-md-6 mb-4">
                                <h5 class="text-warning mb-3 border-bottom border-secondary pb-2">Datos de Contacto</h5>
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Nombre Completo</label>
                                    <input type="text" name="nombre" class="form-control bg-secondary text-light border-0" value="<?php echo $nombre_actual; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Email</label>
                                    <div class="input-group">
                                        <input type="email" id="inputEmail" name="email" class="form-control bg-secondary text-light border-0" value="<?php echo $correo_actual; ?>" readonly required>
                                        <button class="btn btn-outline-warning" type="button" id="btnToggleEmail" onclick="toggleEmailEdit()">
                                            <i class="bi bi-pencil-fill" id="iconEmail"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" id="btnGuardarDatos">Actualizar Datos</button>
                                </div>
                            </div>
                        </div>

                        <hr class="border-secondary my-4">

                        <div class="bg-black p-4 rounded border border-secondary">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-light mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Cambio de Contraseña</h5>
                                <button type="button" class="btn btn-danger btn-sm fw-bold" id="btnShowPass" onclick="mostrarPassword()">
                                    MODIFICAR CLAVE
                                </button>
                            </div>

                            <div id="passContainer" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-secondary">Nueva Contraseña</label>
                                        <input type="password" name="new_password" id="new_pass" class="form-control bg-secondary text-light border-0" placeholder="Nueva clave" onkeyup="validarTiempoReal()">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-secondary">Confirmar Contraseña</label>
                                        <input type="password" name="confirm_password" id="confirm_pass" class="form-control bg-secondary text-light border-0" placeholder="Repetir clave" onkeyup="validarTiempoReal()">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span id="textoValidacion" class="small fw-bold"></span>
                                    
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="ocultarPassword()">Cancelar</button>
                                        <button type="submit" class="btn btn-warning fw-bold" id="btnGuardarPass" disabled>
                                            CONFIRMAR NUEVA CONTRASEÑA
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// 1. Email (Igual que antes)
function toggleEmailEdit() {
    var input = document.getElementById('inputEmail');
    var icon = document.getElementById('iconEmail');
    var btn = document.getElementById('btnToggleEmail');
    
    if (input.readOnly) {
        input.readOnly = false;
        input.focus();
        input.classList.remove('bg-secondary');
        input.classList.add('bg-dark', 'border', 'border-warning');
        icon.classList.replace('bi-pencil-fill', 'bi-check-lg');
        btn.classList.replace('btn-outline-warning', 'btn-success');
    } else {
        input.readOnly = true;
        input.classList.add('bg-secondary');
        input.classList.remove('bg-dark', 'border', 'border-warning');
        icon.classList.replace('bi-check-lg', 'bi-pencil-fill');
        btn.classList.replace('btn-success', 'btn-outline-warning');
    }
}

// 2. Mostrar/Ocultar Contraseña
function mostrarPassword() {
    document.getElementById('passContainer').classList.remove('d-none');
    document.getElementById('btnShowPass').classList.add('d-none');
}

function ocultarPassword() {
    document.getElementById('passContainer').classList.add('d-none');
    document.getElementById('btnShowPass').classList.remove('d-none');
    document.getElementById('new_pass').value = '';
    document.getElementById('confirm_pass').value = '';
    document.getElementById('textoValidacion').innerHTML = '';
}

// 3. VALIDACIÓN EN TIEMPO REAL (La novedad)
function validarTiempoReal() {
    var p1 = document.getElementById('new_pass').value;
    var p2 = document.getElementById('confirm_pass').value;
    var texto = document.getElementById('textoValidacion');
    var btn = document.getElementById('btnGuardarPass');

    // Si ambos campos están vacíos, no mostrar nada
    if (p1 === '' && p2 === '') {
        texto.innerHTML = '';
        btn.disabled = true;
        return;
    }

    // Comparación
    if (p1 === p2 && p1.length > 0) {
        texto.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Las contraseñas coinciden</span>';
        btn.disabled = false; // Habilitar botón
    } else {
        texto.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Las contraseñas NO coinciden</span>';
        btn.disabled = true; // Deshabilitar botón para evitar errores
    }
}

// Función para editar Usuario
function toggleUserEdit() {
    var input = document.getElementById('inputUser');
    var icon = document.getElementById('iconUser');
    var btn = document.getElementById('btnToggleUser');
    var help = document.getElementById('userHelp');
    
    if (input.readOnly) {
        input.readOnly = false;
        input.focus();
        input.classList.remove('bg-secondary');
        input.classList.add('bg-dark', 'border', 'border-warning');
        icon.classList.replace('bi-pencil-fill', 'bi-check-lg');
        btn.classList.replace('btn-outline-warning', 'btn-success');
        help.classList.remove('d-none');
    } else {
        input.readOnly = true;
        input.classList.add('bg-secondary');
        input.classList.remove('bg-dark', 'border', 'border-warning');
        icon.classList.replace('bi-check-lg', 'bi-pencil-fill');
        btn.classList.replace('btn-success', 'btn-outline-warning');
        help.classList.add('d-none');
    }
}

// 4. Envío del Formulario (Común para ambos botones)
document.getElementById('formEditar').addEventListener('submit', function(e) {
    e.preventDefault(); 
    
    var form = e.target;
    var data = new FormData(form);
    var msgDiv = document.getElementById('mensajeRespuesta');
    
    // Feedback visual
    msgDiv.innerHTML = '<div class="alert alert-info bg-dark border-info text-info">Procesando cambios...</div>';

    fetch(form.action, {
        method: form.method,
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            msgDiv.innerHTML = '<div class="alert alert-success bg-dark border-success text-success"><i class="bi bi-check-circle me-2"></i>' + data.exito + '</div>';
            setTimeout(function() {
                window.location.reload(); 
            }, 1500);
        } else {
            msgDiv.innerHTML = '<div class="alert alert-danger bg-dark border-danger text-danger">' + (data.error || 'Error desconocido') + '</div>';
        }
    })
    .catch(error => {
        msgDiv.innerHTML = '<div class="alert alert-danger bg-dark border-danger text-danger">Error de conexión.</div>';
    });
});
</script>