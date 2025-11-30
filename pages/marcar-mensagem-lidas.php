<?php
session_start();
require_once 'config.php';

// Verificar se está logado como artista
if (!isArtista()) {
    echo json_encode(['success' => false]);
    exit();
}

$artista = getArtistaLogado($conn);
$artista_id = $artista['id'];

// Marcar todas as mensagens como lidas
$sql_marcar_lidas = "UPDATE mensagens_artistas SET lida = 1 WHERE artista_id = ? AND lida = 0";
$stmt_marcar = $conn->prepare($sql_marcar_lidas);
$stmt_marcar->bind_param("i", $artista_id);

if ($stmt_marcar->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt_marcar->close();
$conn->close();
?>