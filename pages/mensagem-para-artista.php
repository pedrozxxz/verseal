<?php
session_start();

// Verificar se o usuário está logado como cliente
if (!isset($_SESSION["clientes"])) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado como cliente para enviar mensagens.']);
    exit();
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $artista_id = intval($_POST['artista_id']);
    $cliente_nome = trim($_POST['cliente_nome']);
    $cliente_email = trim($_POST['cliente_email']);
    $mensagem = trim($_POST['mensagem']);

    // Validações
    if (empty($artista_id) || empty($cliente_nome) || empty($cliente_email) || empty($mensagem)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
        exit();
    }

    if (!filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido.']);
        exit();
    }

    // Verificar se o artista existe
    $sql_artista = "SELECT id, nome FROM artistas WHERE id = ? AND ativo = 1";
    $stmt_artista = $conn->prepare($sql_artista);
    $stmt_artista->bind_param("i", $artista_id);
    $stmt_artista->execute();
    $result_artista = $stmt_artista->get_result();

    if ($result_artista->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Artista não encontrado.']);
        exit();
    }

    $artista = $result_artista->fetch_assoc();

    // Inserir mensagem no banco
    $sql_insert = "INSERT INTO mensagens_artistas (artista_id, cliente_nome, cliente_email, mensagem) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $artista_id, $cliente_nome, $cliente_email, $mensagem);

    if ($stmt_insert->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Mensagem enviada com sucesso para ' . $artista['nome'] . '!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem. Tente novamente.']);
    }

    $stmt_insert->close();
    $stmt_artista->close();
}

$conn->close();
?>