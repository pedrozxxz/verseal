<?php
session_start();

function verificarSessaoArtista() {
    // Verifica se é artista logado
    if (isset($_SESSION["artista"]) && is_array($_SESSION["artista"])) {
        return $_SESSION["artista"];
    }
    
    // Redireciona para login se não estiver logado
    header("Location: login.php");
    exit();
}

function getArtistaLogado() {
    if (isset($_SESSION["artista"]) && is_array($_SESSION["artista"])) {
        return $_SESSION["artista"];
    }
    return null;
}
?>