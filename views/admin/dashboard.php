<?php
if(session_status() === PHP_SESSION_NONE) { session_start(); }

// Seguridad
if(!isset($_SESSION['usuario_logueado']) || $_SESSION['rol_usuario'] !== 'admin') {
    echo "<script>alert('ACCESO DENEGADO'); window.location.href='index.php';</script>";
    exit;
}

$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

$productos = [];
$stats = ['total_prod' => 0, 'total_users' => 0, 'total_ventas' => 0, 'ingresos' => 0];

if(!$mysqli->connect_error) {
    // 1. Obtener lista de productos
    $sql = "SELECT * FROM PRODUCTOS";
    $resultado = $mysqli->query($sql);
    while($fila = $resultado->fetch_assoc()) { $productos[] = $fila; }

    // 2. ESTADÍSTICAS (NUEVO)
    
    // Contar Productos
    $sql_p = "SELECT COUNT(*) as total FROM PRODUCTOS";
    $stats['total_prod'] = $mysqli->query($sql_p)->fetch_assoc()['total'];

    // Contar Usuarios (Excluyendo admins si quieres)
    $sql_u = "SELECT COUNT(*) as total FROM CLIENTE WHERE ROL = 'usuario'";
    $stats['total_users'] = $mysqli->query($sql_u)->fetch_assoc()['total'];

    // Contar Ventas Totales
    $sql_v = "SELECT COUNT(*) as total FROM COMPRA";
    $stats['total_ventas'] = $mysqli->query($sql_v)->fetch_assoc()['total'];
    
    // Calcular Ingresos Totales (Suma de precios de productos vendidos)
    // Hacemos un JOIN para sumar el precio de cada producto vendido
    $sql_money = "SELECT SUM(P.PRECIO) as total_bs 
                  FROM COMPRA C 
                  JOIN PRODUCTOS P ON C.COD_PROD = P.COD_PROD";
    $row_money = $mysqli->query($sql_money)->fetch_assoc();
    $stats['ingresos'] = $row_money['total_bs'] ?? 0;
}
?>

<div class="container py-5">
    
    <h2 class="text-danger fw-bold mb-4">PANEL DE CONTROL - ESTADÍSTICAS</h2>

    <div class="row mb-5">
        <div class="col-md-3">
            <div class="card bg-dark border-warning text-light h-100">
                <div class="card-body text-center">
                    <h1 class="text-warning fw-bold"><?php echo $stats['total_prod']; ?></h1>
                    <small class="text-secondary">Suministros Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-info text-light h-100">
                <div class="card-body text-center">
                    <h1 class="text-info fw-bold"><?php echo $stats['total_users']; ?></h1>
                    <small class="text-secondary">Stalkers Registrados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-success text-light h-100">
                <div class="card-body text-center">
                    <h1 class="text-success fw-bold"><?php echo $stats['total_ventas']; ?></h1>
                    <small class="text-secondary">Ventas Realizadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-light text-light h-100">
                <div class="card-body text-center">
                    <h3 class="text-light fw-bold mt-2">Bs. <?php echo number_format($stats['ingresos'], 2); ?></h3>
                    <small class="text-secondary">Ingresos Totales</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Inventario de Armamento</h4>
        <a href="index.php?pagina=admin_crear" class="btn btn-warning fw-bold">
            <i class="bi bi-plus-circle-fill"></i> NUEVO SUMINISTRO
        </a>
    </div>

    <div class="card bg-dark border-secondary shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle text-center">
                    <thead class="bg-black text-warning">
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td>#<?php echo $prod['COD_PROD']; ?></td>
                            <td>
                                <img src="<?php echo $prod['IMAGEN']; ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #555;">
                            </td>
                            <td class="fw-bold text-start"><?php echo $prod['NOMBRE_PRODUCTO']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $prod['CATEGORIA']; ?></span></td>
                            <td class="text-success">Bs. <?php echo number_format($prod['PRECIO'], 2); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="index.php?pagina=admin_editar&id=<?php echo $prod['COD_PROD']; ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="prepararEliminacion(
                                                <?php echo $prod['COD_PROD']; ?>, 
                                                '<?php echo htmlspecialchars($prod['NOMBRE_PRODUCTO'], ENT_QUOTES); ?>', 
                                                '<?php echo $prod['IMAGEN']; ?>'
                                            )">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-danger text-light">
            <div class="modal-header bg-black border-danger">
                <h5 class="modal-title text-danger fw-bold">¿ELIMINAR SUMINISTRO?</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                
                <img id="imgBorrar" src="" alt="Producto" class="rounded mb-3 border border-secondary" style="width: 100px; height: 100px; object-fit: cover;">
                
                <h4 id="nombreBorrar" class="text-light mb-3"></h4>
                
                <p class="text-secondary">
                    Esta acción borrará el producto de la base de datos permanentemente.
                </p>

                <div id="alertaError" class="alert alert-danger d-none text-start small">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                    <span id="textoError"></span>
                </div>

            </div>
            <div class="modal-footer border-danger bg-black">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger fw-bold" id="btnConfirmarBorrar">
                    <i class="bi bi-trash-fill me-2"></i> SÍ, ELIMINAR AHORA
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let idParaBorrar = null; // Variable global para recordar qué ID vamos a borrar
const modalEl = new bootstrap.Modal(document.getElementById('modalEliminar'));
const alerta = document.getElementById('alertaError');
const textoError = document.getElementById('textoError');

// A. Función que abre el modal y llena los datos
function prepararEliminacion(id, nombre, imagen) {
    idParaBorrar = id;
    
    // Llenamos el modal con la info del producto
    document.getElementById('nombreBorrar').textContent = nombre;
    document.getElementById('imgBorrar').src = imagen;
    
    // Reseteamos alertas
    alerta.classList.add('d-none');
    
    // Mostramos el modal
    modalEl.show();
}

// B. Función que ejecuta el borrado cuando das click en "SÍ, ELIMINAR"
document.getElementById('btnConfirmarBorrar').addEventListener('click', function() {
    if (!idParaBorrar) return;

    var datos = new FormData();
    datos.append('id', idParaBorrar);

    fetch('index.php?pagina=api_admin_eliminar', {
        method: 'POST',
        body: datos
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Si se borró, cerramos modal y recargamos
            modalEl.hide();
            location.reload();
        } else {
            // Si falló (ej: Ya comprado), mostramos la alerta DENTRO del modal
            alerta.classList.remove('d-none');
            textoError.textContent = data.mensaje; // Muestra el mensaje de "Protección Activa"
        }
    })
    .catch(err => {
        console.error(err);
        alerta.classList.remove('d-none');
        textoError.textContent = "Error de conexión con el servidor.";
    });
});
</script>