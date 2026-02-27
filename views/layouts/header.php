<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metro Exodus Mods Store</title>
    <link rel="icon" href="img/logo_spartan.jpg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<?php 

// 1. Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$num_itens = count($_SESSION['carrito']);
$badge_class = ($num_itens > 0) ? '' : 'd-none';

// 3. LÓGICA DE USUARIO LIMPIA
// Verificamos si existe la variable 'usuario_logueado'
$esta_logueado = isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true;

// Nombre a mostrar: Solo el ID (ej: CAR123) o el Nombre (ej: CARLOS) de la BD. Nada de apodos inventados.
$nombre_mostrar = "";
if ($esta_logueado) {
    // Preferimos mostrar el USUARIO (Login) en el menú, o el NOMBRE si lo prefieres
    $nombre_mostrar = $_SESSION['id_usuario']; 
}

$es_admin=isset($_SESSION['rol_usuario']) && $_SESSION['rol_usuario'] === 'admin';

?>

<body class="bg-dark text-light"> 
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="img/logo_spartan.png" alt="Logo Metro" class="me-2" style="height: 40px; width: auto; filter: drop-shadow(0px 0px 1px #ffffff);">   
                M|E MODS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?pagina=inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?pagina=mods">Mods</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?pagina=nosotros">Sobre Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?pagina=contacto">Contáctanos</a></li>
                    
                    <?php if($es_admin): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="index.php?pagina=admin_dashboard">
                                <i class="bi bi-shield-lock-fill"></i> PANEL
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-warning position-relative" href="index.php?pagina=carrito">
                            <i class="bi bi-cart-fill me-1"></i> Carrito
                            <span id="contador-carrito" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?php echo $badge_class; ?>">
                                <?php echo $num_itens; ?>
                            </span>
                        </a>
                    </li>

                    <?php if (!$esta_logueado): ?>
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link text-warning fw-bold" href="index.php?pagina=login">
                                <i class="bi bi-person-circle me-1"></i> ACCEDER
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle text-warning fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo strtoupper($nombre_mostrar); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark border-secondary">
                                <li><a class="dropdown-item text-light hover-warning" href="index.php?pagina=perfil"><i class="bi bi-file-earmark-person me-2"></i> Ver Perfil</a></li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?pagina=logout"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">