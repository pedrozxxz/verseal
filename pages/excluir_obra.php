<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit();
}

$usuarioLogado = $_SESSION["usuario"];
$nomeUsuario = $usuarioLogado['nome'] ?? '';

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $obraId = intval($_POST['obra_id'] ?? 0);
    
    if ($acao === 'excluir' && $obraId > 0) {
        // Buscar informações da obra para excluir a imagem
        $sql_select = "SELECT imagem_url FROM produtos WHERE id = ? AND artista = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("is", $obraId, $nomeUsuario);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $obra = $result->fetch_assoc();
        
        if ($obra) {
            // Excluir a imagem do servidor se não for a imagem padrão
            if (!empty($obra['imagem_url']) && $obra['imagem_url'] !== 'img/imagem2.png') {
                $caminho_imagem = '../' . $obra['imagem_url'];
                if (file_exists($caminho_imagem)) {
                    unlink($caminho_imagem);
                }
            }
            
            // Excluir a obra do banco de dados
            $sql_delete = "DELETE FROM produtos WHERE id = ? AND artista = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("is", $obraId, $nomeUsuario);
            
            if ($stmt_delete->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Obra excluída com sucesso']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir a obra do banco de dados']);
            }
            
            $stmt_delete->close();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Obra não encontrada ou você não tem permissão para excluí-la']);
        }
        
        $stmt_select->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
?>