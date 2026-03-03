<?php
define('views_path', '../views/');

// 1. Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'inicio';
include views_path . 'layouts/header.php';

switch ($pagina) {
    case 'admin_editar':
        include views_path . 'pages/admin_editar.php';
        break;
    
    case 'inicio':
        include views_path . 'pages/inicio.php';
        break;
    case 'mods':
        include views_path . 'pages/mods.php';
        break;
    case 'detalle':
        include views_path.'pages/detalle.php';
        break;
    case 'nosotros':
        include views_path . 'pages/nosotros.php';
        break;
    case 'contacto':
        include views_path . 'pages/contacto.php';
        break;
    case 'carrito':
        include views_path.'pages/carrito.php';
        break;
    case 'checkout':
        include views_path.'pages/checkout.php';
        break;
    case 'login':
        include views_path.'auth/login.php';
        break;
    case 'registro':
        include views_path.'auth/registro.php';
        break;
    case 'recuperar': 
        include views_path.'auth/recuperar.php';
        break;
    case 'perfil':
        include views_path.'pages/perfil.php';
        break;
    case 'validar':
        require_once '../app/controller/loginV2.php';
        break;
    case 'api_registro':
        require_once '../app/controller/api_registrarse.php';
        exit; // <--- ¡ESTO ES OBLIGATORIO!
        break;
    case 'api_actualizar':
        require_once '../app/controller/api_actualizar_perfil.php';
        exit;
        break;
    case 'api_admin_actualizar':
        require_once '../app/controller/api_admin_actualizar.php';
        exit;
        break;
    case 'api_wishlist':
        require_once '../app/controller/api_wishlist.php';
        exit;
        break;
    case 'api_contacto':
        require_once '../app/controller/api_contacto.php';
        exit;
        break;
    case 'admin_dashboard':
        include views_path.'admin/dashboard.php';
        break;
    case 'api_admin_crear':
        require_once '../app/controller/api_admin_crear.php';
        exit;
        break;
    case 'admin_crear':
        include views_path.'admin/crear.php';
        break;
    case 'api_admin_eliminar':
        require_once '../app/controller/api_admin_eliminar.php';
        exit;
        break;
    case 'logout':
        // 1. Destrói todas as variáveis de sessão
        $_SESSION = array(); 
        
        // 2. Invalida o cookie de sessão (opcional, mas recomendado)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // 3. Finaliza a sessão
        session_destroy();
        
        // 4. Redireciona o usuário para a página inicial
        header('Location: index.php');
        exit(); // Importante para garantir que o script pare de executar
        
    break;
        
    case 'editar_perfil':
        include views_path.'pages/editar_perfil.php';
        break;
    default:
        echo "<div class='alert alert-danger text-center'>Error 404: Página no encontrada.</div>";

        break;
}

include views_path . 'layouts/footer.php';
?>