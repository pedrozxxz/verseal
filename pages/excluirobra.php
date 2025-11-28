<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $conn->connect_error]);
    exit();
}

// Verificar se foi enviado o ID da obra
if (!isset($_POST['obra_id']) || empty($_POST['obra_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da obra não informado']);
    exit();
}

$obra_id = intval($_POST['obra_id']);
$usuarioLogado = $_SESSION["usuario"];

// Verificar se a obra pertence ao usuário logado
$sql_verificar = "SELECT id, artista FROM produtos WHERE id = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("i", $obra_id);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Obra não encontrada']);
    exit();
}

$obra = $result_verificar->fetch_assoc();

// Verificar se o usuário logado é o artista da obra
if ($obra['artista'] !== $usuarioLogado['nome']) {
    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta obra']);
    exit();
}

// Excluir a obra
$sql_excluir = "DELETE FROM produtos WHERE id = ?";
$stmt_excluir = $conn->prepare($sql_excluir);
$stmt_excluir->bind_param("i", $obra_id);

if ($stmt_excluir->execute()) {
    echo json_encode(['success' => true, 'message' => 'Obra excluída com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir obra: ' . $conn->error]);
}

$stmt_excluir->close();
$stmt_verificar->close();
$conn->close();
?>