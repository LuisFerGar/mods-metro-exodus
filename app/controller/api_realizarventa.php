<?php
// Define o cabeçalho para indicar que a resposta é JSON
header('Content-Type: application/json');

// Garante que a sessão seja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Configurações do Banco de Dados ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario = "root";
$contrasena = "";
$port = 3306;

// Variável de resposta padrão
$response = [
    'status' => 'error',
    'message' => 'Método inválido o datos incompletos.',
];

// 1. Validação do Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Solo se permiten peticiones POST.';
    echo json_encode($response);
    exit();
}

// 2. Conexão com o Banco de Dados
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    $response['message'] = "Error de conexcion en la base de datos! Detalhes: " . $mysqli->connect_error;
    echo json_encode($response);
    exit();
}

// 3. Obtém e valida dados enviados via POST (pela página checkout.php)
$total_venda = isset($_POST['total']) ? (float)$_POST['total'] : 0;
$produtos_json = $_POST['produtos_json'] ?? null;
$produtos_carrinho = json_decode($produtos_json, true);

// ====================================================================================
// CORREÇÃO CRÍTICA: LÊ O ID DO USUÁRIO A PARTIR DO POST (Enviado pelo checkout.php)
$usuario_comprador = $_POST['usuario_id'] ?? null;
// ====================================================================================

$compras_registradas = 0;

if (!$usuario_comprador) {
    $response['message'] = 'No se proporcionó el ID del comprador. No se pudo procesar la transacción.';
    $mysqli->close();
    echo json_encode($response);
    exit();
}

if ($total_venda > 0 && is_array($produtos_carrinho) && !empty($produtos_carrinho)) {
 
    // Inicia a transação (garante que todas as queries sejam executadas ou nenhuma)
    $mysqli->begin_transaction();
 
    try {
        // SQL para INSERIR NA TABELA 'COMPRA'
        $sql_compra = "INSERT INTO COMPRA (FECHA, USUARIO, COD_PROD) VALUES (CURDATE(), ?, ?)";
        $stmt_compra = $mysqli->prepare($sql_compra);
 

        if ($stmt_compra === false) {
            
    throw new Exception("Erro ao preparar statement de COMPRA: " . $mysqli->error);
            }
 

        // Loop por cada item do carrinho
        foreach ($produtos_carrinho as $produto) {
            $cod_prod = $produto['id'];
 
    
            // Associa (bind) os parâmetros (s=string, i=integer)
            $stmt_compra->bind_param("si", $usuario_comprador, $cod_prod);
 
    
            if (!$stmt_compra->execute()) {
                // Se houver erro, verifica se é o erro de chave duplicada (1062)
                if ($stmt_compra->errno == 1062) {
                    
            // Lança uma exceção específica para tratar no catch
                     
                throw new Exception("Duplicate Entry Error (1062): Tentativa de recomprar COD_PROD $cod_prod.");
                    } else {
                    
            // Para qualquer outro erro de DB, lança exceção genérica
                     
                throw new Exception("Erro ao inserir COMPRA para COD_PROD $cod_prod: " . $stmt_compra->error);
                    }
            }
            $compras_registradas++;
        }
 

        $stmt_compra->close();
 

        // SUCESSO: Confirma a transação e define a resposta
        $mysqli->commit();
        $response['status'] = 'success';
        $response['message'] = "Compra registrada correctamente. Total de activos digitales adquiridos: $compras_registradas.";
        $response['total_registrado'] = $total_venda;
 

        // Limpa o carrinho APÓS O SUCESSO. (O carrinho está na sessão do cliente)
        unset($_SESSION['carrito']); 
 

    } catch (Exception $e) {
        // FALHA: Reverte a transação
        $mysqli->rollback();
 

        // Tratamento da exceção de Chave Duplicada (Ativo já comprado)
        if (strpos($e->getMessage(), 'Duplicate Entry Error (1062)') !== false) {
            
    $response['message'] = "Compra fallida: Ya tienes uno o más artículos de este pedido y no puedes volver a comprarlos, ya que son activos digitales únicos. Elimina los mods que ya tengas de tu carrito y vuelve a intentarlo.";
            } else {
            
    // Tratamento de outros erros de transação
             
        $response['message'] = "Falha na transação de compra. Motivo: " . $e->getMessage();
            }
    }
 
} else {
    $response['message'] = 'Dados de compra incompletos ou inválidos (Total zero, Carrinho vazio ou ID do usuário ausente).';
}

$mysqli->close();
// O uso de JSON_UNESCAPED_UNICODE é bom para garantir acentos corretos
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>