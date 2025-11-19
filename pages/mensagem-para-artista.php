<?php
session_start();
header('Content-Type: application/json');

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão com o banco de dados: ' . $conn->connect_error]);
    exit;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se recebeu os dados
if (!isset($_POST['artista_id']) || !isset($_POST['cliente_nome']) || !isset($_POST['cliente_email']) || !isset($_POST['mensagem'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Validar dados
$artista_id = intval($_POST['artista_id']);
$cliente_nome = trim($_POST['cliente_nome']);
$cliente_email = trim($_POST['cliente_email']);
$mensagem = trim($_POST['mensagem']);

if (empty($cliente_nome) || empty($cliente_email) || empty($mensagem) || $artista_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

// Buscar cliente_id da sessão se existir
$cliente_id = 0;
if (isset($_SESSION['clientes']) && is_array($_SESSION['clientes']) && isset($_SESSION['clientes']['id'])) {
    $cliente_id = intval($_SESSION['clientes']['id']);
}

// Verificar se artista existe
$sql_artista = "SELECT id, nome FROM artistas WHERE id = ? AND ativo = 1";
$stmt_artista = $conn->prepare($sql_artista);
$stmt_artista->bind_param("i", $artista_id);
$stmt_artista->execute();
$result_artista = $stmt_artista->get_result();

if ($result_artista->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Artista não encontrado']);
    exit;
}

// Inserir mensagem no banco
$sql_insert = "INSERT INTO mensagens_artistas (artista_id, cliente_id, cliente_nome, cliente_email, mensagem) 
               VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

if ($stmt_insert === false) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar query: ' . $conn->error]);
    exit;
}

$stmt_insert->bind_param("iisss", $artista_id, $cliente_id, $cliente_nome, $cliente_email, $mensagem);

if ($stmt_insert->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso! O artista entrará em contato em breve.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao enviar mensagem: ' . $stmt_insert->error
    ]);
}

$stmt_artista->close();
if ($stmt_insert) $stmt_insert->close();
$conn->close();
?>