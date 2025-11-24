<?php
// config.php

// Verificar se a sessão já foi iniciada antes de chamar session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "verseal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Função para verificar se é artista
function isArtista() {
    return isset($_SESSION["artistas"]);
}

// Função para verificar se é usuário comum
function isUsuario() {
    return isset($_SESSION["usuario"]);
}

// Função para obter dados do usuário logado
function getUsuarioLogado($conn) {
    if (isArtista()) {
        $artistaId = $_SESSION["artistas"]['id'];
        $sql = "SELECT *, 'artista' as tipo FROM artistas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $artistaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados = $result->fetch_assoc();
        $stmt->close();
        return $dados;
    } elseif (isUsuario()) {
        $usuario_nome = is_array($_SESSION["usuario"]) ? $_SESSION["usuario"]['nome'] : $_SESSION["usuario"];
        $sql = "SELECT *, 'usuario' as tipo FROM usuarios WHERE nome = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario_nome);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados = $result->fetch_assoc();
        $stmt->close();
        return $dados;
    }
    return null;
}

// Função para forçar atualização da imagem (evitar cache)
function getImagemComTimestamp($caminho_imagem) {
    if (empty($caminho_imagem)) {
        return '../img/jamile.jpg';
    }
    
    $caminho_completo = '../' . $caminho_imagem;
    if (file_exists($caminho_completo)) {
        return $caminho_completo . '?t=' . filemtime($caminho_completo);
    }
    
    return $caminho_completo;
}

// ========== VERIFICAÇÃO DO USUÁRIO LOGADO ==========
// Verificar se usuário está logado (cliente ou artista)
$usuarioLogado = null;
$tipoUsuario = null;

// Verifica se há sessão de artista
if (isArtista()) {
    $usuarioLogado = $_SESSION["artistas"];
    $tipoUsuario = "artista";
}
// Verifica se há sessão de usuário comum
elseif (isUsuario()) {
    // Se for string, converter para array
    if (is_string($_SESSION["usuario"])) {
        $usuarioLogado = ['nome' => $_SESSION["usuario"]];
    } else {
        $usuarioLogado = $_SESSION["usuario"];
    }
    $tipoUsuario = "usuario";
}
?>