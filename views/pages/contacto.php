<div class="container py-5">

    <h2 class="text-center text-warning display-5 fw-bold mb-5">Centro de Comunicaciones</h2>

    <div class="row">
        
        <div class="col-lg-7 mb-5">
            <div class="card bg-dark border-secondary shadow-lg">
                <div class="card-header bg-black border-secondary text-warning">
                    <h5 class="mb-0 text-warning">📡 Enviar Transmisión de Radio</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form id="formContacto" action="index.php?pagina=api_contacto" method="POST">
                        
                        <div id="mensajeAlerta" class="mb-3" style="display: none;"></div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label text-secondary">Nombre en Clave (Stalker ID)</label>
                            <input type="text" class="form-control bg-secondary text-light border-0" id="nombre" name="nombre" placeholder="Ej: Artyom" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary">Frecuencia de Respuesta (Email)</label>
                            <input type="email" class="form-control bg-secondary text-light border-0" id="email" name="email" placeholder="nombre@metro.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="asunto" class="form-label text-secondary">Tipo de Solicitud</label>
                            <select class="form-select bg-secondary text-light border-0" id="asunto" name="asunto">
                                <option selected>Realizar Pedido</option>
                                <option>Reportar Mutante Avistado</option>
                                <option>Solicitud de Unión a la Tripulación</option>
                                <option>Soporte Técnico</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="mensaje" class="form-label text-secondary">Mensaje Cifrado</label>
                            <textarea class="form-control bg-secondary text-light border-0" id="mensaje" name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí... cambio." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning fw-bold btn-lg" id="btnEnviar">
                                <i class="bi bi-send-fill me-2"></i> TRANSMITIR MENSAJE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-body">
                    <h4 class="mb-4">Coordenadas del Aurora</h4>
                    
                    <div class="d-flex align-items-start mb-4"> 
                        <span class="h4 me-3 text-secondary"><i class="bi bi-geo-alt-fill"></i></span>
                        <div>
                            <h6 class="text-warning mb-1">UBICACIÓN ACTUAL:</h6>
                            <p class="text-secondary fs-5 mb-0">
                                Vías del Transiberiano, Km 3450.<br>
                                Cerca del Valle del Bosque (Taiga).
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <span class="h4 me-3 text-secondary"><i class="bi bi-broadcast-pin"></i></span>
                        <div>
                            <h6 class="text-warning mb-1">FRECUENCIA DE ESCUCHA:</h6>
                            <p class="text-secondary fs-5 mb-0">
                                AM 108.0 kHz<br>
                                Disponible de 20:00 a 06:00 (Horario Nocturno)
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <span class="h4 me-3 text-secondary"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        <div>
                            <h6 class="text-warning mb-1">ADVERTENCIA:</h6>
                            <p class="text-secondary fs-5 mb-0">
                                No transmitas si estás cerca de anomalías eléctricas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-black border-secondary p-1">
                <img src="img/contacto/mapa.jpg" 
                     alt="Mapa del Metro" 
                     class="img-fluid opacity-75" 
                     style="filter: sepia(50%) hue-rotate(-30deg);"> 
                
                <div class="card-footer bg-black border-0 text-center">
                    <small class="text-light opacity-50">Mapa Táctico - Primavera 2036</small>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('formContacto').addEventListener('submit', function(e) {
    e.preventDefault();

    var form = e.target;
    var data = new FormData(form);
    var btn = document.getElementById('btnEnviar');
    var alerta = document.getElementById('mensajeAlerta');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Transmitiendo...';
    alerta.style.display = 'none';

    fetch(form.action, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-2"></i> TRANSMITIR MENSAJE';
        alerta.style.display = 'block';

        // CORRECCIÓN 1: 'status' (con S) en lugar de 'statud'
        if (data.status === 'success') {
            // CORRECCIÓN 2: Agregamos el signo '=' que faltaba
            alerta.innerHTML = `<div class="alert alert-success bg-dark border-success text-success">
                                    <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                                </div>`;
            form.reset();
        } else {
            // CORRECCIÓN 3: Agregamos el signo '=' que faltaba aquí también
            alerta.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.message}
                                </div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = 'REINTENTAR TRANSMISIÓN';
        alerta.style.display = 'block';
        // CORRECCIÓN 4: Agregamos el signo '='
        alerta.innerHTML = `<div class="alert alert-danger bg-dark border-danger text-danger">
                                Error de comunicación con el servidor.
                            </div>`;
    });
});
</script>