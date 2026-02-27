<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['usuario_logueado']) || $_SESSION['rol_usuario'] !== 'admin') {
    header('Location: index.php?pagina=login');
    exit();
}

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black border-secondary d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">NUEVO SUMINISTRO</h3>
                    <a href="index.php?pagina=admin_dashboard" class="btn btn-sm btn-outline-secondary">Cancelar</a>
                </div>
                <div class="card-body p-4">
                    
                    <form id="formCrear" action="index.php?pagina=api_admin_crear" method="POST" enctype="multipart/form-data">
                        
                        <div id="mensajeRespuesta" class="mb-3"></div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary">Nombre del Producto</label>
                                <input type="text" name="nombre" class="form-control bg-secondary text-light border-0" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-secondary">Precio (Bs.)</label>
                                <input type="number" step="0.01" name="precio" class="form-control bg-secondary text-light border-0" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label text-secondary">Categoría</label>
                                <select name="categoria" class="form-select bg-secondary text-light border-0">
                                    <option value="Armas">Armas</option>
                                    <option value="Trajes">Trajes</option>
                                    <option value="Suministros">Suministros</option>
                                    <option value="Skins">Skins Visuales</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Descripción Técnica</label>
                            <textarea name="descripcion" class="form-control bg-secondary text-light border-0" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary">Imagen del Producto</label>
                            <input type="file" name="imagen" class="form-control bg-secondary text-light border-0" accept="image/*" required>
                            <small class="text-muted">Formatos aceptados: JPG, PNG, GIF.</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning fw-bold" id="btnGuardar">REGISTRAR EN LA BASE DE DATOS</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formCrear').addEventListener('submit', function(e) {
    e.preventDefault(); // 1. Detenemos el envío normal (para que no salga la pantalla negra)
    
    var form = e.target;
    var data = new FormData(form); // Empaquetamos texto + imagen
    var btn = document.getElementById('btnGuardar');
    var msg = document.getElementById('mensajeRespuesta');
    
    // Estado de carga
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Subiendo archivos...';
    msg.innerHTML = '';
    
    // 2. Enviamos por Fetch
    fetch(form.action, {
        method: 'POST',
        body: data
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = 'REGISTRAR EN LA BASE DE DATOS';
        
        if(data.status === 'success') {
            // ÉXITO
            msg.innerHTML = `<div class="alert alert-success bg-dark border-success text-success">
                                <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                             </div>`;
            form.reset(); // Limpiamos el formulario
        } else {
            // ERROR
            msg.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.message}
                             </div>`;
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = 'REGISTRAR EN LA BASE DE DATOS';
        msg.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">
                            Error de comunicación con el servidor.
                         </div>`;
    });
});
</script>