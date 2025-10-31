<?php
$host = '127.0.0.1';
$dbname = 'verseal';
$username = 'root'; // Altere conforme seu ambiente
$password = ''; // Altere conforme seu ambiente

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>