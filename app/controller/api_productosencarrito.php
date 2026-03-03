<?php
// Define o cabeçalho para indicar que a resposta é JSON
header('Content-Type: application/json');

// --- Configurações de Conexão (Conforme Exemplo do Usuário) ---
$hostname = "localhost";
$basedatos = "metro_bd";
$usuario = "root";
$contrasena = "";
$port = 3306;

// Função auxiliar para passar parâmetros por referência, 
// necessária para o mysqli::bind_param com um número dinâmico de IDs.
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) // PHP >= 5.3.0
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

// Array de resposta padrão
$response = [
    'status' => 'error',
    'message' => 'No se proporcionó ningún ID de producto o el método no es válido.',
    'produtos' => []
];

// 1. Conexão com o Banco de Dados (usando mysqli)
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos, $port);

if ($mysqli->connect_error) {
    $response['message'] = "ERRO DE CONEXÃO COM A BASE DE DADOS! Detalhes: " . $mysqli->connect_error;
    echo json_encode($response);
    exit();
}

// O carrito.php envia os IDs dos produtos via POST em formato JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_ids'])) {
    
    $ids_json = $_POST['product_ids'];
    $ids_array = json_decode($ids_json, true);

    if (is_array($ids_array) && !empty($ids_array)) {
        
        // 2. Limpeza e preparação dos IDs para a consulta SQL
        // Converte cada ID para inteiro (segurança) e remove duplicatas
        $cleaned_ids = array_map('intval', array_unique($ids_array));
        $num_ids = count($cleaned_ids);
        
        // Cria placeholders (ex: ?, ?, ?) para a cláusula IN
        $placeholders = implode(',', array_fill(0, $num_ids, '?'));
        
        // 3. Montagem da consulta SQL
        $sql = "SELECT COD_PROD, NOMBRE_PRODUCTO, PRECIO 
                FROM PRODUCTOS 
                WHERE COD_PROD IN ($placeholders)";
        
        // 4. Preparação e execução da consulta
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            // Cria a string de tipos (ex: 'iii' para 3 IDs inteiros)
            $types = str_repeat('i', $num_ids);
            
            // Adiciona a string de tipos ao início do array de IDs para bind_param
            $bind_params = array_merge([$types], $cleaned_ids);
            
            // Chama bind_param dinamicamente usando a função auxiliar
            call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));
            
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $produtos = [];
                    while ($row = $result->fetch_assoc()) {
                        $produtos[] = $row;
                    }

                    // Formata a resposta com sucesso
                    $response['status'] = 'success';
                    $response['message'] = 'Productos encontrados exitosamente.';
                    
                    // Indexa o array de produtos pelo COD_PROD para fácil mapeamento na view
                    $produtos_indexados = [];
                    foreach ($produtos as $prod) {
                        $produtos_indexados[$prod['COD_PROD']] = $prod;
                    }
                    $response['produtos'] = $produtos_indexados;
                } else {
                    $response['message'] = 'No se proporcionó ningún ID de producto o el método no es válido.';
                }
                $result->free();
            } else {
                 $response['message'] = 'Erro ao executar a consulta: ' . $mysqli->error;
            }
            $stmt->close();
        } else {
             $response['message'] = 'Erro na preparação da consulta: ' . $mysqli->error;
        }
    } else {
        $response['message'] = 'Formato de IDs de produto inválido ou array vazio.';
    }
}

$mysqli->close();
echo json_encode($response);
?>