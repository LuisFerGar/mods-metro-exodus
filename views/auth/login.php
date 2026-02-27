<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black border-secondary text-center">
                    <h3 class="mb-0">IDENTIFICACIÓN</h3>
                </div>
                <div class="card-body p-4">

                <?php 
                    if (isset($_GET['error'])) {
                        $error_code = $_GET['error'];
                        $mensaje_error = "";
                            
                      if ($error_code == 1) {
                            $mensaje_error = "Usuario no encontrado en los registros del Aurora.";
                     } elseif ($error_code == 2) {
                          $mensaje_error = "Código de acceso (contraseña) incorrecto.";
                       }
                            
                        if ($mensaje_error) {
                            echo '<div class="alert alert-danger text-center border-danger bg-dark text-danger mb-4">';
                            echo '<i class="bi bi-exclamation-triangle-fill me-2"></i> ' . $mensaje_error;
                            echo '</div>';
                        }
                    }
                ?>
                    
                    <form action="index.php?pagina=validar" method="POST">
                        
                        <div class="mb-4">
                            <label for="usuario" class="form-label text-secondary">Nombre en Clave (Usuario)</label>
                            <input type="text" name="usuario" id="usuario" class="form-control bg-secondary text-light border-0" placeholder="Ej: Artyom" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary">Código de Acceso (Contraseña)</label>
                            <input type="password" name="password" id="password" class="form-control bg-secondary text-light border-0" required>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-warning fw-bold">INICIAR SESIÓN</button>
                        </div>

                        <div class="text-center">
                            <p class="small mb-1">¿No tienes credenciales?</p>
                            <a href="index.php?pagina=registro" class="text-warning text-decoration-none">Solicitar Ingreso (Registrarse)</a>
                            <div class="mt-2">
                                <a href="index.php?pagina=recuperar" class="text-secondary small text-decoration-none">¿Olvidaste tu código?</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>