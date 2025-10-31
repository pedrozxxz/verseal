<?php
session_start();

// Configurações de debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Permitir CORS se necessário
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Log para debug
error_log("=== EXCLUIR OBRA INICIADO ===");

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    error_log("Usuário não logado");
    echo json_encode(['success' => false, 'message' => 'Usuário não logado. Faça login novamente.']);
    exit();
}

$usuarioLogado = $_SESSION["usuario"];
error_log("Usuário: " . $usuarioLogado['nome']);

// Verificar se é uma requisição POST válida
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Método não permitido: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Verificar dados POST
if (!isset($_POST['acao']) || $_POST['acao'] !== 'excluir' || !isset($_POST['obra_id'])) {
    error_log("Dados POST inválidos: " . print_r($_POST, true));
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

$obra_id = intval($_POST['obra_id']);
error_log("Tentando excluir obra ID: " . $obra_id);

if ($obra_id <= 0) {
    error_log("ID da obra inválido: " . $obra_id);
    echo json_encode(['success' => false, 'message' => 'ID da obra inválido']);
    exit();
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    // Verificar se a obra existe e pertence ao usuário
    $sql_verificar = "SELECT id, nome FROM obras WHERE id = ? AND artista = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("is", $obra_id, $usuarioLogado['nome']);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        error_log("Obra não encontrada: ID $obra_id, Artista: " . $usuarioLogado['nome']);
        echo json_encode(['success' => false, 'message' => 'Obra não encontrada ou não pertence a você']);
        exit();
    }
    
    $obra = $result_verificar->fetch_assoc();
    error_log("Obra encontrada: " . $obra['nome']);
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // 1. Excluir categorias primeiro
        $sql_categorias = "DELETE FROM obra_categoria WHERE obra_id = ?";
        $stmt_categorias = $conn->prepare($sql_categorias);
        $stmt_categorias->bind_param("i", $obra_id);
        $stmt_categorias->execute();
        error_log("Categorias excluídas");
        
        // 2. Excluir a obra
        $sql_obra = "DELETE FROM obras WHERE id = ? AND artista = ?";
        $stmt_obra = $conn->prepare($sql_obra);
        $stmt_obra->bind_param("is", $obra_id, $usuarioLogado['nome']);
        $stmt_obra->execute();
        
        if ($stmt_obra->affected_rows > 0) {
            $conn->commit();
            error_log("Obra excluída com sucesso");
            echo json_encode(['success' => true, 'message' => 'Obra excluída com sucesso']);
        } else {
            $conn->rollback();
            error_log("Nenhuma linha afetada na exclusão");
            echo json_encode(['success' => false, 'message' => 'Erro: Nenhuma obra foi excluída']);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

error_log("=== EXCLUIR OBRA FINALIZADO ===");
?>
