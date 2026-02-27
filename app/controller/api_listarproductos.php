<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
ob_clean(); // Limpieza vital
header('Content-Type: application/json; charset=utf-8');

// Define o cabeçalho para indicar que a resposta é JSON
header('Content-Type: application/json');

// --- Configurações do Banco de Dados ---
$hostname = "localhost"; 
$basedatos = "metro_bd"; 
$usuario = "root"; 
$contrasena = "";

// Array para armazenar a resposta da API (inclui status, mensagem e os dados dos produtos)
$response = array();
$response['productos'] = [];
$response['status'] = 'error'; // Status inicial de erro

// Conexão com o Banco de Dados
$mysqli = new mysqli($hostname, $usuario, $contrasena, $basedatos);

if ($mysqli->connect_error) {
    // SE ISSO FALHAR, SEU ERRO É AQUI. Verifique se o MySQL/MariaDB está rodando e se a DB "metro3" existe.
    $response['message'] = "ERRO DE CONEXÃO COM A BASE DE DADOS! Detalhes: " . $mysqli->connect_error;
} else {
    // Consulta SQL para recuperar todos os produtos
    $sql_productos = "SELECT * FROM PRODUCTOS";
    
    // Executa a consulta
    $result_productos = $mysqli->query($sql_productos);

    if ($result_productos) {
        if ($result_productos->num_rows > 0) {
            // Se houver productos, itera sobre os resultados e armazena no array
            $productos = [];
            while ($row = $result_productos->fetch_assoc()) {
                $productos[] = $row;
            }
            
            // Define o array de productos e o status de sucesso
            $response['productos'] = $productos;
            $response['status'] = 'success';
            $response['message'] = 'Productos recuperados con éxito.';
            
            $result_productos->free();
        } else {
            // Se a tabela estiver vazia (Sucesso na consulta, mas sem resultados)
            $response['message'] = 'Nenhum produto encontrado na tabela PRODUCTOS.';
            $response['status'] = 'success';
        }
    } else {
        // SE ISSO FALHAR, SEU ERRO É NA QUERY (Ex: tabela inexistente).
        $response['message'] = 'ERRO SQL! Tabela PRODUCTOS pode estar faltando em "metro3". Detalhes: ' . $mysqli->error;
    }
}

// Fecha a conexão com o banco de dados
$mysqli->close();

// Retorna a resposta em formato JSON. 
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>