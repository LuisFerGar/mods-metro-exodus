</div> <footer class="bg-black text-secondary py-5 mt-5 border-top border-secondary">
    <div class="container">
        <div class="row">
           <div class="col-md-4 mb-4">
    <div class="d-flex align-items-center mb-3">
        <img src="img/logo_spartan.png" alt="Logo Spartan" class="me-3" style="height: 100px; width: auto; filter: drop-shadow(0px 0px 1px #ffffff);">
        
        <h5 class="mb-0">M|E MODS</h5>
    </div>

    <p class="small text-secondary">
        Tu parada obligatoria antes de salir a la superficie. Suministros probados en combate para Spartans y Stalkers.
    </p>
</div>

    <div class="col-md-8">
        
        <h5 class="text-light mb-4 text-center">ENLACES RÁPIDOS</h5>

        <div class="row">
            
            <div class="col-md-6 mb-3 text-center">
                    <li class="mb-2">
                        <a href="index.php?pagina=inicio" class="text-secondary text-decoration-none hover-warning">
                            <i class="bi bi-chevron-right small me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?pagina=mods" class="text-secondary text-decoration-none hover-warning">
                            <i class="bi bi-chevron-right small me-1"></i> Catálogo de Mods
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?pagina=nosotros" class="text-secondary text-decoration-none hover-warning">
                            <i class="bi bi-chevron-right small me-1"></i> Sobre la Tripulación
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?pagina=contacto" class="text-secondary text-decoration-none hover-warning">
                            <i class="bi bi-chevron-right small me-1"></i> Centro de Radio
                        </a>
                    </li>
                </ul>
                </ul>
            </div>

            <div class="col-md-6 mb-3 text-center">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php?pagina=login" class="text-secondary text-decoration-none hover-warning">
                            <i class="bi bi-person-circle me-1"></i> Iniciar Sesión
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-secondary text-decoration-none hover-warning" data-bs-toggle="modal" data-bs-target="#modalTerminosGlobal">
                            <i class="bi bi-file-earmark-text-fill me-1"></i> Términos de Servicio
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-secondary text-decoration-none hover-warning" data-bs-toggle="modal" data-bs-target="#modalPrivacidad">
                            <i class="bi bi-shield-lock-fill me-1"></i> Política de Privacidad
                        </a>
                    </li>
                </ul>
            </div>

        </div> 
    </div>

        <div class="text-center small">
            <p class="mb-1 text-light">© 2025 Metro Exodus Mod Store</p>
            <p class="text-muted">Proyecto Educativo</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script> 
    function toggleWishlist(btn, idProducto) {
        var datos = new FormData();
        datos.append('producto_id', idProducto);
        fetch('index.php?pagina=api_wishlist', {
            method: 'POST',
            body: datos
        })
        .then(response=> response.json())
        .then(data => {
            if(data.status === 'added') {
                btn.classList.remove('btn-outline-secondary', 'text-secondary');
                btn.classList.add('btn-danger', 'text-white');
                btn.innerHTML='<i class="bi bi-heart-fill"></i>';
            }else if(data.status === 'removed') {
                btn.classList.remove('btn-danger', 'text-white');
                btn.classList.add('btn-outline-secondary', 'text-secondary');
                btn.innerHTML='<i class="bi bi-heart"></i>';
            }else if(data.status === 'error') {
                alert(data.message);
                if(data.message.includes("iniciar sesión")) {
                    window.location.href='index.php?pagina=login';
                }
            }
        })
        .catch(error => {
            console.error('Error de conexión', error);
        });
    }

    function eliminarDeseoPerfil(btn, idProducto) {
        var datos = new FormData();
        datos.append('producto_id', idProducto);

        fetch('index.php?pagina=api_wishlist', {
            method: 'POST',
            body: datos
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'removed') {
                var fila = document.getElementById('deseo-' + idProducto);
                if(fila) {
                    fila.remove();
                }
            }else{
                alert('Error al eliminar: ' +data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>


</body>
</html>

<div class="modal fade" id="modalTerminosGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"> <div class="modal-content bg-dark border-secondary text-light shadow-lg">
            <div class="modal-header border-secondary bg-black">
                <h5 class="modal-title" style="font-family: 'Russo One', sans-serif;">TÉRMINOS DE SERVICIO DEL METRO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="text-warning fw-bold">1. ACEPTACIÓN DE RIESGOS</h6>
                <p class="text-secondary">Al comerciar con M|E MODS, usted reconoce que la superficie es un lugar hostil. No nos hacemos responsables de paquetes interceptados por demonios alados, bandidos o anomalías eléctricas durante el tránsito.</p>
                
                <h6 class="text-warning fw-bold mt-4">2. MONEDA DE CAMBIO</h6>
                <p class="text-secondary">Todas las transacciones se calculan en base al valor de la munición de grado militar (calibre 5.45). Nos reservamos el derecho de ajustar precios según la inflación de la Hansa.</p>
                
                <h6 class="text-warning fw-bold mt-4">3. MODIFICACIONES ILEGALES</h6>
                <p class="text-secondary">El uso de nuestros mods para atacar estaciones neutrales o civiles resultará en el bloqueo permanente de su cuenta y una orden de búsqueda por parte de la Orden Espartana.</p>
                
                <h6 class="text-warning fw-bold mt-4">4. GARANTÍA LIMITADA</h6>
                <p class="text-secondary">Las armas restauradas tienen una garantía de 30 días. Esto no cubre daños por inmersión en pantanos, radiación extrema o mordeduras de Nosalis.</p>
            </div>
            <div class="modal-footer border-secondary bg-black">
                <button type="button" class="btn btn-warning fw-bold" data-bs-dismiss="modal">Entendido, cambio y fuera</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPrivacidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content bg-dark border-secondary text-light shadow-lg">
            <div class="modal-header border-secondary bg-black">
                <h5 class="modal-title" style="font-family: 'Russo One', sans-serif;">POLÍTICA DE ENCRIPTACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="lead text-light fs-5">En M|E MODS, el silencio de radio es nuestra prioridad. Sus datos están seguros con nosotros.</p>
                
                <hr class="border-secondary my-4">

                <h6 class="text-warning fw-bold">RECOPILACIÓN DE DATOS</h6>
                <p class="text-secondary">Solo guardamos su frecuencia de radio (email) y su nombre en clave. No rastreamos su ubicación geográfica para evitar que facciones enemigas encuentren su escondite.</p>

                <h6 class="text-warning fw-bold mt-4">COMPARTIR INFORMACIÓN</h6>
                <p class="text-secondary">M|E MODS nunca venderá sus datos a la Hansa, a la Línea Roja ni a los Observadores. Sus secretos mueren con nosotros.</p>

                <h6 class="text-warning fw-bold mt-4">COOKIES Y RASTREADORES</h6>
                <p class="text-secondary">Utilizamos cookies solo para recordar qué hay en su mochila (carrito). No utilizamos tecnología de rastreo del viejo mundo.</p>
            </div>
            <div class="modal-footer border-secondary bg-black">
                <button type="button" class="btn btn-warning fw-bold" data-bs-dismiss="modal">Acepto el Silencio de Radio</button>
            </div>
        </div>
    </div>
</div>
