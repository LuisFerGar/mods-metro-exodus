<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-body p-5 text-center">
                    <h3 class="mb-3">¿COMUNICACIÓN PERDIDA?</h3>
                    <p class="text-secondary mb-4">Ingresa tu frecuencia de radio (email) y te enviaremos las nuevas claves de cifrado.</p>
                    
                    <form action="index.php?action=recuperar" method="POST">
                        <div class="mb-4">
                            <input type="email" name="email" class="form-control bg-secondary text-light border-0" placeholder="tu@email.com" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-warning fw-bold">ENVIAR INSTRUCCIONES</button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <a href="index.php?pagina=login" class="text-muted small text-decoration-none">&larr; Volver al Login</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>