<?php
// Garante que a sessão seja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// --- LÓGICA DE REMOÇÃO DE ITEM ---
// Verifica se o ID do produto a ser removido foi passado na URL
if (isset($_GET['remove_id']) && is_numeric($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];
    
    if (isset($_SESSION['carrito'][$remove_id])) {
        // Remove o ID do produto do carrinho
        unset($_SESSION['carrito'][$remove_id]);
    }
    
    // Redireciona para limpar o parâmetro da URL (Padrão PRG) e atualizar a página
    header('Location: index.php?pagina=carrito');
    exit(); 
}
// --- FIM LÓGICA DE REMOÇÃO DE ITEM ---

// Obtém os IDs dos produtos da sessão
$produtos_no_carrinho = $_SESSION['carrito'];
$lista_ids_para_api = array_keys($produtos_no_carrinho);
$produtos_detalhados = [];
// *** NOVO: Array para armazenar apenas o necessário para a API de Venda ***
$produtos_para_venda = []; 
$subtotal = 0;
$total = 0;
$mensagem_carrinho = '';

// Se o carrinho estiver vazio, define a mensagem
if (empty($lista_ids_para_api)) {
    $mensagem_carrinho = 'Tu carrito está vacío. ¡Es hora de equiparte!';
    // *** NOVO: Limpa dados de venda antigos se o carrinho estiver vazio ***
    unset($_SESSION['dados_venda']);
} else {
    // URL da API que buscará os detalhes dos produtos por ID (Usando o seu nome de arquivo)
    $api_url = 'http://localhost/MetroModsStore/app/controller/api_productosencarrito.php';

    // Prepara o payload para enviar os IDs via POST
    $payload = http_build_query([
        'product_ids' => json_encode($lista_ids_para_api) 
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $payload,
        ],
    ];
    $context  = stream_context_create($options);
    $json_data = @file_get_contents($api_url, false, $context);

    if ($json_data === false) {
        $mensagem_carrinho = 'Error al conectar con el servicio de detalles de productos.';
    } else {
        $data = json_decode($json_data, true);
        
        if (isset($data['status']) && $data['status'] === 'success' && !empty($data['produtos'])) {
            $produtos_detalhados_api = $data['produtos'];

            // Mapeia os dados da API com a quantidade fixa de 1 e calcula o subtotal
            foreach ($produtos_detalhados_api as $id => $prod) {
                // Quantidade fixada em 1, conforme a lógica de equipamento único
                $quantidade = 1; 
                
                $preco = (float)$prod['PRECIO'];
                $total_item = $preco * $quantidade;
                
                $prod['QUANTIDADE'] = $quantidade;
                $prod['TOTAL_ITEM'] = $total_item;
                
                $produtos_detalhados[$id] = $prod; // Array para a view
                
                // *** NOVO: Array MINIMALISTA para a API de VENDA ***
                $produtos_para_venda[] = [
                    'id' => (int)$id,
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                    'subtotal' => $total_item
                ];
                // *************************************************
                
                $subtotal += $total_item;
            }
            $total = $subtotal; // Assume 0% de desconto/taxa por simplicidade
            
            // ******* NOVO: ARMAZENA DADOS DA VENDA NA SESSÃO *******
            // Isso garante que o 'checkout.php' possa acessar os dados calculados e válidos.
            $_SESSION['dados_venda'] = [
                'produtos' => $produtos_para_venda,
                'total' => $total,
            ];
            // *******************************************************
            
        } else {
            $mensagem_carrinho = 'Error: No se pudieron obtener los detalles de los productos...' . ($data['message'] ?? 'N/A');
        }
    }
}
?>

<div class="container py-5">
    <h2 class="display-5 text-warning fw-bold mb-5 text-center">Tu Equipamiento</h2>

    <?php if (!empty($mensagem_carrinho)): ?>
        <div class="alert alert-info text-center bg-dark border-warning text-warning" role="alert">
            <i class="bi bi-info-circle me-2"></i> <?php echo $mensagem_carrinho; ?>
            <p class="mt-2 mb-0"><a href="index.php?pagina=mods" class="text-warning fw-bold text-decoration-underline">Ir al Catálogo</a></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($produtos_detalhados)): ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-dark border-secondary shadow-lg mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr class="text-secondary border-secondary">
                                    <th scope="col" class="p-4">Producto</th>
                                    <th scope="col">Precio Unitario</th>
                                    <th scope="col" class="text-end p-4">Total (1 Unidad)</th>
                                    <th scope="col" class="p-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_detalhados as $id => $item): ?>
                                <tr>
                                    <td class="p-4">
                                        <div class="d-flex align-items-center">
                                            <!-- Placeholder dinâmico -->
                                            <img src="https://placehold.co/60x60/333333/ffffff?text=<?php echo urlencode(substr($item['NOMBRE_PRODUCTO'], 0, 8)); ?>" class="rounded border border-secondary me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0 text-warning"><?php echo htmlspecialchars($item['NOMBRE_PRODUCTO']); ?></h6>
                                                <small class="mb-0 text-secondary fs-6">ID: <?php echo htmlspecialchars($id); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Bs. <?php echo number_format($item['PRECIO'], 2, '.', ','); ?></td>
                                    <!-- O Total é o Preço Unitário -->
                                    <td class="text-end p-4 fw-bold">Bs. <?php echo number_format($item['TOTAL_ITEM'], 2, '.', ','); ?></td>
                                    <td>
                                        <!-- Link para remover o item da sessão -->
                                        <a href="index.php?pagina=carrito&remove_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar de tu equipamiento">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <a href="index.php?pagina=mods" class="text-secondary text-decoration-none">
                &larr; Seguir buscando suministros
            </a>
            
        </div>

        <div class="col-lg-4">
            <div class="card bg-black border-secondary p-4 sticky-top" style="top: 20px;">
                <h4 class="mb-4">Resumen</h4>
                
                <div class="d-flex justify-content-between mb-2 text-secondary">
                    <span>Subtotal</span>
                    <span>Bs. <?php echo number_format($subtotal, 2, '.', ','); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4 text-success">
                    <span>Descuento</span>
                    <span>- Bs. 0.00</span>
                </div>
                <hr class="border-secondary">
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="h4 text-light">Total a Pagar</span>
                    <span class="h4 text-warning fw-bold">Bs. <?php echo number_format($total, 2, '.', ','); ?></span>
                </div>

                <!-- Botão de Confirmação que enviará os dados para a próxima etapa (checkout) -->
                <form method="POST" action="index.php?pagina=checkout"> 
                    <button type="submit" class="btn btn-warning w-100 fw-bold btn-lg mb-3">CONFIRMAR COMPRA</button>
                </form>
                
                <div class="text-center">
                   <small class="text-muted">Pagos seguros vía Hansa</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>