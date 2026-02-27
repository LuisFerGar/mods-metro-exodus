<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black border-secondary text-center">
                    <h3 class="text-warning mb-0">NUEVO RECLUTA</h3>
                </div>
                <div class="card-body p-4">
                    
                    <form id="formRegistro" action="index.php?pagina=api_registro" method="POST">

                        <div id="mensajeRespuesta" class="mb-3"></div>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control bg-secondary text-light border-0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Frecuencia de Radio (Email)</label>
                            <input type="email" name="correo" class="form-control bg-secondary text-light border-0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Apodo (Usuario)</label>
                            <input type="text" name="usuario" class="form-control bg-secondary text-light border-0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Contraseña</label>
                            <input type="password" name="password" class="form-control bg-secondary text-light border-0" required>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input bg-secondary border-0" type="checkbox" id="terminos" required>
                            <label class="form-check-label text-secondary small" for="terminos">
                                Juro lealtad a la Orden Espartana 
                                (<a href="#" class="text-warning text-decoration-underline" data-bs-toggle="modal" data-bs-target="#modalTerminosGlobal">Términos</a>)
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning fw-bold" id="btnRegistro">UNIRSE AL AURORA</button>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="index.php?pagina=login" class="text-muted small text-decoration-none">&larr; Volver al Login</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault(); 
    
    var form = e.target;
    var data = new FormData(form);
    var btn = document.getElementById('btnRegistro');
    var msgDiv = document.getElementById('mensajeRespuesta');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...';
    msgDiv.innerHTML = '';

    fetch(form.action, {
        method: form.method,
        body: data
    })
    .then(response => response.json()) // Esperamos JSON limpio
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = 'UNIRSE AL AURORA';
        
        if (data.exito) {
            // ÉXITO (Verde Oscuro)
            msgDiv.innerHTML = `
                <div class="alert alert-success bg-dark border-success text-success mt-3">
                    <i class="bi bi-check-circle-fill me-2"></i> ${data.exito}
                </div>`;
            
            form.reset();
            setTimeout(function() {
                window.location.href = 'index.php?pagina=inicio';
            }, 2000);

        } else if (data.error) {
            // ERROR (Rojo Oscuro - Estilo Metro)
            msgDiv.innerHTML = `
                <div class="alert alert-danger bg-dark border-danger text-danger mt-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.error}
                </div>`;
        } else {
            msgDiv.innerHTML = '<div class="alert alert-warning bg-dark border-warning text-warning mt-3">Error desconocido.</div>';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = 'UNIRSE AL AURORA';
        // ERROR DE CONEXIÓN (Rojo Oscuro)
        msgDiv.innerHTML = `
            <div class="alert alert-danger bg-dark border-danger text-danger mt-3">
                <i class="bi bi-wifi-off me-2"></i> Error de comunicación con el búnker (API).
            </div>`;
        console.error('Error:', error);
    });
});
</script>