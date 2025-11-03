<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para enviar mensagens.']);
    exit;
}

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artistaId = intval($_POST['artista_id']);
    $artistaNome = trim($_POST['artista_nome']);
    $assunto = trim($_POST['assunto']);
    $mensagem = trim($_POST['mensagem']);
    
    $remetenteNome = is_array($_SESSION['usuario']) ? $_SESSION['usuario']['nome'] : $_SESSION['usuario'];
    
    // Buscar ID do artista
    $sqlArtista = "SELECT id FROM artistas WHERE id = ? AND nome = ?";
    $stmtArtista = $conn->prepare($sqlArtista);
    $stmtArtista->bind_param("is", $artistaId, $artistaNome);
    $stmtArtista->execute();
    $resultArtista = $stmtArtista->get_result();
    
    if ($resultArtista->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Artista não encontrado.']);
        exit;
    }
    
    $artista = $resultArtista->fetch_assoc();
    $destinatarioId = $artista['id'];
    
    // Inserir mensagem
    $sqlMensagem = "INSERT INTO mensagens (remetente_id, destinatario_id, assunto, mensagem, tipo) VALUES (?, ?, ?, ?, 'artista_usuario')";
    $stmtMensagem = $conn->prepare($sqlMensagem);
    $stmtMensagem->bind_param("iiss", $remetenteNome, $destinatarioId, $assunto, $mensagem);
    
    if ($stmtMensagem->execute()) {
        // Criar notificação para o artista
        $tituloNotificacao = "Nova mensagem de $remetenteNome";
        $mensagemNotificacao = "Você recebeu uma nova mensagem sobre: $assunto";
        $linkNotificacao = "notificacoes.php";
        
        $sqlNotificacao = "INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, link) VALUES (?, ?, ?, 'nova_mensagem', ?)";
        $stmtNotificacao = $conn->prepare($sqlNotificacao);
        $stmtNotificacao->bind_param("isss", $destinatarioId, $tituloNotificacao, $mensagemNotificacao, $linkNotificacao);
        $stmtNotificacao->execute();
        
        echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem.']);
    }
    
    $stmtMensagem->close();
    $stmtNotificacao->close();
}

$conn->close();
?>